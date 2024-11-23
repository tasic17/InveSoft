<?php
namespace app\controllers;

use app\core\Application;
use app\core\BaseController;
use app\models\ProizvodModel;
use app\models\ZalihaModel;
use app\models\PromenaZalihaModel;
use app\models\KategorijaModel;

class InventoryController extends BaseController {
    public function overview() {
        $proizvodModel = new ProizvodModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Base query
        $baseQuery = "FROM proizvodi p 
                     JOIN kategorije k ON p.kategorijaID = k.kategorijaID
                     JOIN zalihe z ON p.proizvodID = z.proizvodID";

        // Add search condition if search term exists
        $searchCondition = "";
        $params = [];
        if (!empty($search)) {
            $searchCondition = " WHERE p.naziv LIKE ? OR p.opis LIKE ? OR k.naziv LIKE ?";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total " . $baseQuery . $searchCondition;
        $stmt = $proizvodModel->con->prepare($countQuery);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $totalResult = $stmt->get_result()->fetch_assoc();
        $total = $totalResult['total'];
        $totalPages = ceil($total / $limit);

        // Get paginated results
        $query = "SELECT p.proizvodID, p.naziv, p.opis, p.cena, k.naziv as kategorija, z.kolicina "
            . $baseQuery . $searchCondition
            . " ORDER BY p.proizvodID LIMIT ? OFFSET ?";

        $stmt = $proizvodModel->con->prepare($query);
        if (!empty($params)) {
            $params[] = $limit;
            $params[] = $offset;
            $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', ...$params);
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (isset($_GET['ajax'])) {
            // If it's an AJAX request, return JSON
            header('Content-Type: application/json');
            echo json_encode([
                'items' => $results,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            exit;
        }

        // Regular page load
        $this->view->render('inventory/overview', 'main', [
            'items' => $results,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }

    public function addProduct() {
        if (!$this->isAdmin()) {
            Application::$app->session->set('errorNotification', 'Pristup nije dozvoljen!');
            header("location:" . "/inventory");
            exit;
        }

        $kategorije = new KategorijaModel();
        $kategorije = $kategorije->all("");

        $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije]);
    }

    public function processAddProduct() {
        if (!$this->isAdmin()) {
            Application::$app->session->set('errorNotification', 'Pristup nije dozvoljen!');
            header("location:" . "/inventory");
            exit;
        }

        $proizvodModel = new ProizvodModel();
        $zalihaModel = new ZalihaModel();

        $proizvodModel->mapData($_POST);

        // Additional validation for non-negative values
        if (isset($_POST['pocetna_kolicina']) && $_POST['pocetna_kolicina'] < 0) {
            Application::$app->session->set('errorNotification', 'Početna količina ne može biti negativna!');
            $this->view->render('inventory/addProduct', 'main', $proizvodModel);
            exit;
        }

        if (isset($_POST['cena']) && $_POST['cena'] < 0) {
            Application::$app->session->set('errorNotification', 'Cena ne može biti negativna!');
            $this->view->render('inventory/addProduct', 'main', $proizvodModel);
            exit;
        }

        $proizvodModel->validate();

        if ($proizvodModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri dodavanju proizvoda!');
            $this->view->render('inventory/addProduct', 'main', $proizvodModel);
            exit;
        }

        // Start transaction
        $proizvodModel->con->begin_transaction();

        try {
            // Insert the product
            if (!$proizvodModel->insert()) {
                throw new \Exception("Error inserting product: " . $proizvodModel->con->error);
            }

            // Get the last inserted product ID
            $proizvodID = $proizvodModel->con->insert_id;

            // Create initial inventory record
            $zalihaModel->proizvodID = $proizvodID;
            $zalihaModel->kolicina = (int)max(0, $_POST['pocetna_kolicina'] ?? 0); // Ensure non-negative

            if (!$zalihaModel->insert()) {
                throw new \Exception("Error inserting inventory: " . $zalihaModel->con->error);
            }

            // If everything is successful, commit the transaction
            $proizvodModel->con->commit();

            Application::$app->session->set('successNotification', 'Proizvod uspešno dodat!');
            header("location:" . "/inventory");
        } catch (\Exception $e) {
            // If there's an error, rollback the transaction
            $proizvodModel->con->rollback();

            Application::$app->session->set('errorNotification', 'Greška: ' . $e->getMessage());
            $this->view->render('inventory/addProduct', 'main', $proizvodModel);
            exit;
        }
    }

    public function updateStock() {
        if (!isset($_GET['id'])) {
            header("location:" . "/inventory");
            exit;
        }

        $proizvodModel = new ProizvodModel();
        $proizvodModel->one("where proizvodID = " . $_GET['id']);

        $this->view->render('inventory/updateStock', 'main', $proizvodModel);
    }

    public function processUpdateStock() {
        $promenaModel = new PromenaZalihaModel();
        $zalihaModel = new ZalihaModel();

        $promenaModel->mapData($_POST);
        $promenaModel->korisnikID = Application::$app->session->get('user')[0]['id'];
        $promenaModel->datum_promene = date('Y-m-d H:i:s');

        // Additional validation for non-negative values
        if (isset($_POST['kolicina']) && $_POST['kolicina'] < 0) {
            Application::$app->session->set('errorNotification', 'Količina ne može biti negativna!');
            $this->view->render('inventory/updateStock', 'main', $promenaModel);
            exit;
        }

        $promenaModel->validate();

        if ($promenaModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri ažuriranju zaliha!');
            $this->view->render('inventory/updateStock', 'main', $promenaModel);
            exit;
        }

        // Start transaction
        $promenaModel->con->begin_transaction();

        try {
            // Get current stock level
            $zalihaModel->one("where proizvodID = $promenaModel->proizvodID");

            // Calculate new quantity
            $newQuantity = $promenaModel->tip_promene === 'Ulaz'
                ? $zalihaModel->kolicina + (int)$promenaModel->kolicina
                : $zalihaModel->kolicina - (int)$promenaModel->kolicina;

            // Check if we have enough stock for outgoing transactions
            if ($promenaModel->tip_promene === 'Izlaz' && $newQuantity < 0) {
                throw new \Exception("Nedovoljna količina na zalihama!");
            }

            // Update stock
            $zalihaModel->kolicina = $newQuantity;
            if (!$zalihaModel->update("where proizvodID = $promenaModel->proizvodID")) {
                throw new \Exception("Greška pri ažuriranju zaliha: " . $zalihaModel->con->error);
            }

            // Record stock change
            if (!$promenaModel->insert()) {
                throw new \Exception("Greška pri beleženju promene: " . $promenaModel->con->error);
            }

            // If everything is successful, commit the transaction
            $promenaModel->con->commit();

            Application::$app->session->set('successNotification', 'Zalihe uspešno ažurirane!');
            header("location:" . "/inventory");
        } catch (\Exception $e) {
            // If there's an error, rollback the transaction
            $promenaModel->con->rollback();

            Application::$app->session->set('errorNotification', 'Greška: ' . $e->getMessage());
            $this->view->render('inventory/updateStock', 'main', $promenaModel);
            exit;
        }
    }

    public function stockHistory() {
        $promenaModel = new PromenaZalihaModel();

        $query = "SELECT pz.*, p.naziv as proizvod, k.ime as korisnik, pz.datum_promene 
                 FROM promene_zaliha pz
                 JOIN proizvodi p ON pz.proizvodID = p.proizvodID
                 JOIN korisnici k ON pz.korisnikID = k.korisnikID
                 ORDER BY pz.datum_promene DESC";

        $results = $promenaModel->executeQuery($query);

        $this->view->render('inventory/history', 'main', $results);
    }

    private function isAdmin(): bool {
        $user = Application::$app->session->get('user');
        if (!$user) {
            return false;
        }

        foreach ($user as $userData) {
            if ($userData['role'] === 'Administrator') {
                return true;
            }
        }

        return false;
    }

    public function accessRole(): array {
        return ['Administrator', 'Radnik'];
    }
}