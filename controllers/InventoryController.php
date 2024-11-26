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
            header('Content-Type: application/json');
            echo json_encode([
                'items' => $results,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            exit;
        }

        $this->view->render('inventory/overview', 'main', [
            'items' => $results,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'isAdmin' => $this->isAdmin() // Add this line to pass admin status to view
        ]);
    }

    public function stockReport() {
        if (!$this->isAdmin()) {
            Application::$app->session->set('errorNotification', 'Pristup nije dozvoljen!');
            header("location:" . "/inventory");
            exit;
        }

        $promenaModel = new PromenaZalihaModel();

        // Get date filters from request
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

        // Base query for stock changes with daily aggregation
        $stockChangesQuery = "SELECT 
        DATE(pz.datum_promene) as date,
        pz.tip_promene,
        SUM(pz.kolicina) as total_kolicina
    FROM promene_zaliha pz";

        // Add date filters if provided
        if ($startDate && $endDate) {
            $stockChangesQuery .= " WHERE DATE(pz.datum_promene) BETWEEN '$startDate' AND '$endDate'";
        }

        $stockChangesQuery .= " GROUP BY DATE(pz.datum_promene), pz.tip_promene
        ORDER BY pz.datum_promene ASC";

        $stockChanges = $promenaModel->executeQuery($stockChangesQuery);

        // Reorganize stock changes data for the bar chart
        $stockData = [];
        foreach ($stockChanges as $change) {
            $date = $change['date'];
            if (!isset($stockData[$date])) {
                $stockData[$date] = [
                    'ulaz' => 0,
                    'izlaz' => 0
                ];
            }
            if ($change['tip_promene'] === 'Ulaz') {
                $stockData[$date]['ulaz'] = intval($change['total_kolicina']);
            } else {
                $stockData[$date]['izlaz'] = intval($change['total_kolicina']);
            }
        }
        ksort($stockData);

        // Get detailed stock changes for the timeline
        $detailedStockQuery = "SELECT 
        DATE(pz.datum_promene) as date,
        p.naziv as product_name,
        pz.tip_promene,
        pz.kolicina,
        k.naziv as category_name
    FROM promene_zaliha pz
    JOIN proizvodi p ON pz.proizvodID = p.proizvodID
    JOIN kategorije k ON p.kategorijaID = k.kategorijaID";

        if ($startDate && $endDate) {
            $detailedStockQuery .= " WHERE DATE(pz.datum_promene) BETWEEN '$startDate' AND '$endDate'";
        }

        $detailedStockQuery .= " ORDER BY pz.datum_promene ASC";

        $detailedChanges = $promenaModel->executeQuery($detailedStockQuery);

        // Get current stock by category
        $categoryStockQuery = "SELECT 
        k.naziv as category_name,
        SUM(z.kolicina) as total_quantity,
        COUNT(p.proizvodID) as product_count
    FROM kategorije k
    JOIN proizvodi p ON k.kategorijaID = p.kategorijaID
    JOIN zalihe z ON p.proizvodID = z.proizvodID
    GROUP BY k.kategorijaID, k.naziv
    ORDER BY total_quantity DESC";

        $categoryStock = $promenaModel->executeQuery($categoryStockQuery);

        // Get product distribution within categories
        $productDistributionQuery = "SELECT 
        k.naziv as category_name,
        p.naziv as product_name,
        z.kolicina as quantity
    FROM kategorije k
    JOIN proizvodi p ON k.kategorijaID = p.kategorijaID
    JOIN zalihe z ON p.proizvodID = z.proizvodID
    ORDER BY k.naziv, z.kolicina DESC";

        $productDistribution = $promenaModel->executeQuery($productDistributionQuery);

        // Process data for category-specific charts
        $productsByCategory = [];
        foreach ($productDistribution as $product) {
            $categoryName = $product['category_name'];
            if (!isset($productsByCategory[$categoryName])) {
                $productsByCategory[$categoryName] = [
                    'labels' => [],
                    'data' => []
                ];
            }
            $productsByCategory[$categoryName]['labels'][] = $product['product_name'];
            $productsByCategory[$categoryName]['data'][] = intval($product['quantity']);
        }

        // Sort each category's data by quantity
        foreach ($productsByCategory as &$category) {
            $combined = array_map(function($label, $value) {
                return ['label' => $label, 'value' => $value];
            }, $category['labels'], $category['data']);

            usort($combined, function($a, $b) {
                return $b['value'] - $a['value'];
            });

            $category['labels'] = array_column($combined, 'label');
            $category['data'] = array_column($combined, 'value');
        }
        unset($category);

        // Handle AJAX requests
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'stockData' => $stockData,
                'detailedChanges' => $detailedChanges,
                'categoryStock' => $categoryStock,
                'productDistribution' => $productDistribution
            ]);
            exit;
        }

        // Render the view with all data
        $this->view->render('inventory/stockReport', 'main', [
            'stockData' => $stockData,
            'detailedChanges' => $detailedChanges,
            'categoryStock' => $categoryStock,
            'productsByCategory' => $productsByCategory
        ]);
    }

    public function searchProducts() {
        if (!isset($_GET['term'])) {
            echo json_encode([]);
            exit;
        }

        $term = $_GET['term'];
        $proizvodModel = new ProizvodModel();

        $query = "SELECT proizvodID, naziv 
                 FROM proizvodi 
                 WHERE naziv LIKE ? 
                 ORDER BY naziv 
                 LIMIT 10";

        $stmt = $proizvodModel->con->prepare($query);
        $searchTerm = "%$term%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }

    public function productHistory() {
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'No product ID provided']);
            exit;
        }

        $productId = $_GET['id'];
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
        $promenaModel = new PromenaZalihaModel();

        // Base query for current stock and first transaction date
        $query = "SELECT 
                z.kolicina as current_stock,
                MIN(pz.datum_promene) as first_change_date
            FROM zalihe z
            LEFT JOIN promene_zaliha pz ON z.proizvodID = pz.proizvodID
            WHERE z.proizvodID = ?";

        $stmt = $promenaModel->con->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $initialInfo = $stmt->get_result()->fetch_assoc();

        // Base query for stock changes
        $query = "SELECT 
                DATE(datum_promene) as date,
                tip_promene,
                kolicina
            FROM promene_zaliha
            WHERE proizvodID = ?";

        // Add date filters if provided
        if ($startDate && $endDate) {
            $query .= " AND DATE(datum_promene) BETWEEN ? AND ?";
        }

        $query .= " ORDER BY datum_promene, promenaID";

        $stmt = $promenaModel->con->prepare($query);
        if ($startDate && $endDate) {
            $stmt->bind_param("iss", $productId, $startDate, $endDate);
        } else {
            $stmt->bind_param("i", $productId);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $dates = [];
        $inflow = [];
        $outflow = [];
        $totalStock = [];

        // Calculate initial stock by reversing all transactions
        $allTransactions = [];
        $runningTotal = $initialInfo['current_stock'];

        while ($row = $result->fetch_assoc()) {
            $allTransactions[] = $row;
            if ($row['tip_promene'] === 'Ulaz') {
                $runningTotal -= (int)$row['kolicina'];
            } else {
                $runningTotal += (int)$row['kolicina'];
            }
        }

        $initialStock = $runningTotal;

        // Add initial point before any changes
        if ($initialInfo['first_change_date']) {
            $firstChangeDate = date('Y-m-d', strtotime($initialInfo['first_change_date']));
            $startDate = date('Y-m-d', strtotime($firstChangeDate . ' -1 day'));

            $dates[] = $startDate;
            $inflow[] = null;
            $outflow[] = null;
            $totalStock[] = $initialStock;
        }

        // Process all transactions
        $runningTotal = $initialStock;
        foreach ($allTransactions as $transaction) {
            $dates[] = $transaction['date'];

            if ($transaction['tip_promene'] === 'Ulaz') {
                $inflow[] = (int)$transaction['kolicina'];
                $outflow[] = null;
                $runningTotal += (int)$transaction['kolicina'];
            } else {
                $inflow[] = null;
                $outflow[] = (int)$transaction['kolicina'];
                $runningTotal -= (int)$transaction['kolicina'];
            }

            $totalStock[] = $runningTotal;
        }

        // If there are no movements, just show current stock
        if (empty($dates)) {
            $dates[] = date('Y-m-d');
            $inflow[] = null;
            $outflow[] = null;
            $totalStock[] = $initialInfo['current_stock'];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'dates' => $dates,
            'inflow' => $inflow,
            'outflow' => $outflow,
            'totalStock' => $totalStock
        ]);
        exit;
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

        // Debug POST data
        var_dump($_POST);

        $proizvodModel->mapData([
            'naziv' => $_POST['naziv'],
            'opis' => $_POST['opis'],
            'cena' => $_POST['cena'],
            'kategorijaID' => $_POST['kategorijaID']
        ]);

        // Additional validation for non-negative values
        if (isset($_POST['pocetna_kolicina']) && $_POST['pocetna_kolicina'] < 0) {
            Application::$app->session->set('errorNotification', 'Početna količina ne može biti negativna!');
            $kategorije = new KategorijaModel();
            $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije->all("")]);
            exit;
        }

        if (isset($_POST['cena']) && $_POST['cena'] < 0) {
            Application::$app->session->set('errorNotification', 'Cena ne može biti negativna!');
            $kategorije = new KategorijaModel();
            $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije->all("")]);
            exit;
        }

        $proizvodModel->validate();

        if ($proizvodModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri dodavanju proizvoda!');
            $kategorije = new KategorijaModel();
            $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije->all("")]);
            exit;
        }

        // Start transaction
        $proizvodModel->con->begin_transaction();

        try {
            // Insert the product
            $query = "INSERT INTO proizvodi (naziv, opis, cena, kategorijaID) VALUES (?, ?, ?, ?)";
            $stmt = $proizvodModel->con->prepare($query);
            $stmt->bind_param("ssdi",
                $proizvodModel->naziv,
                $proizvodModel->opis,
                $proizvodModel->cena,
                $proizvodModel->kategorijaID
            );

            if (!$stmt->execute()) {
                throw new \Exception("Error inserting product: " . $proizvodModel->con->error);
            }

            // Get the last inserted product ID
            $proizvodID = $proizvodModel->con->insert_id;

            // Create initial inventory record
            $pocetnaKolicina = (int)max(0, $_POST['pocetna_kolicina'] ?? 0);

            $query = "INSERT INTO zalihe (proizvodID, kolicina) VALUES (?, ?)";
            $stmt = $proizvodModel->con->prepare($query);
            $stmt->bind_param("ii", $proizvodID, $pocetnaKolicina);

            if (!$stmt->execute()) {
                throw new \Exception("Error inserting inventory: " . $proizvodModel->con->error);
            }

            // Record initial stock as a change if quantity > 0
            if ($pocetnaKolicina > 0) {
                $query = "INSERT INTO promene_zaliha (proizvodID, korisnikID, datum_promene, tip_promene, kolicina) 
                     VALUES (?, ?, NOW(), 'Ulaz', ?)";
                $stmt = $proizvodModel->con->prepare($query);
                $korisnikID = Application::$app->session->get('user')[0]['id'];
                $stmt->bind_param("iii", $proizvodID, $korisnikID, $pocetnaKolicina);

                if (!$stmt->execute()) {
                    throw new \Exception("Error recording initial stock: " . $proizvodModel->con->error);
                }
            }

            // If everything is successful, commit the transaction
            $proizvodModel->con->commit();

            Application::$app->session->set('successNotification', 'Proizvod uspešno dodat!');
            header("location:" . "/inventory");
            exit;
        } catch (\Exception $e) {
            // If there's an error, rollback the transaction
            $proizvodModel->con->rollback();

            Application::$app->session->set('errorNotification', 'Greška: ' . $e->getMessage());
            $kategorije = new KategorijaModel();
            $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije->all("")]);
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

        $query = "SELECT pz.*, p.naziv as proizvod, k.ime as korisnik 
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

    public function deleteProduct() {
        if (!$this->isAdmin()) {
            Application::$app->session->set('errorNotification', 'Pristup nije dozvoljen!');
            header("location:" . "/inventory");
            exit;
        }

        if (!isset($_GET['id'])) {
            Application::$app->session->set('errorNotification', 'ID proizvoda nije prosleđen!');
            header("location:" . "/inventory");
            exit;
        }

        $proizvodID = $_GET['id'];
        $proizvodModel = new ProizvodModel();

        // Start transaction
        $proizvodModel->con->begin_transaction();

        try {
            // First delete from promene_zaliha
            $query = "DELETE FROM promene_zaliha WHERE proizvodID = ?";
            $stmt = $proizvodModel->con->prepare($query);
            $stmt->bind_param("i", $proizvodID);
            $stmt->execute();

            // Then delete from zalihe
            $query = "DELETE FROM zalihe WHERE proizvodID = ?";
            $stmt = $proizvodModel->con->prepare($query);
            $stmt->bind_param("i", $proizvodID);
            $stmt->execute();

            // Finally delete from proizvodi
            $query = "DELETE FROM proizvodi WHERE proizvodID = ?";
            $stmt = $proizvodModel->con->prepare($query);
            $stmt->bind_param("i", $proizvodID);
            $stmt->execute();

            $proizvodModel->con->commit();
            Application::$app->session->set('successNotification', 'Proizvod je uspešno obrisan!');
        } catch (\Exception $e) {
            $proizvodModel->con->rollback();
            Application::$app->session->set('errorNotification', 'Greška pri brisanju proizvoda: ' . $e->getMessage());
        }

        header("location:" . "/inventory");
        exit;
    }

    public function accessRole(): array {
        return ['Administrator', 'Radnik'];
    }
}