<?php
// controllers/InventoryController.php
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
        $zalihaModel = new ZalihaModel();

        $query = "SELECT p.proizvodID, p.naziv, p.opis, p.cena, k.naziv as kategorija, z.kolicina 
                 FROM proizvodi p 
                 JOIN kategorije k ON p.kategorijaID = k.kategorijaID
                 JOIN zalihe z ON p.proizvodID = z.proizvodID";

        $results = $proizvodModel->executeQuery($query);

        $this->view->render('inventory/overview', 'main', $results);
    }

    public function addProduct() {
        $kategorije = new KategorijaModel();
        $kategorije = $kategorije->all("");

        $this->view->render('inventory/addProduct', 'main', ['kategorije' => $kategorije]);
    }

    public function processAddProduct() {
        $proizvodModel = new ProizvodModel();
        $zalihaModel = new ZalihaModel();

        $proizvodModel->mapData($_POST);
        $proizvodModel->validate();

        if ($proizvodModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri dodavanju proizvoda!');
            $this->view->render('inventory/addProduct', 'main', $proizvodModel);
            exit;
        }

        $proizvodModel->insert();

        // Get the last inserted product ID
        $proizvodModel->one("where naziv = '$proizvodModel->naziv'");

        // Create initial inventory record
        $zalihaModel->proizvodID = $proizvodModel->proizvodID;
        $zalihaModel->kolicina = $_POST['pocetna_kolicina'] ?? 0;
        $zalihaModel->insert();

        Application::$app->session->set('successNotification', 'Proizvod uspešno dodat!');
        header("location:" . "/inventory");
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
        $promenaModel->korisnikID = Application::$app->session->get('user')[0]['id_user'];
        $promenaModel->datum_promene = date('Y-m-d H:i:s');

        $promenaModel->validate();

        if ($promenaModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri ažuriranju zaliha!');
            $this->view->render('inventory/updateStock', 'main', $promenaModel);
            exit;
        }

        // Update stock
        $zalihaModel->one("where proizvodID = $promenaModel->proizvodID");
        if ($promenaModel->tip_promene === 'Ulaz') {
            $zalihaModel->kolicina += $promenaModel->kolicina;
        } else {
            $zalihaModel->kolicina -= $promenaModel->kolicina;
        }

        $zalihaModel->update("where proizvodID = $promenaModel->proizvodID");
        $promenaModel->insert();

        Application::$app->session->set('successNotification', 'Zalihe uspešno ažurirane!');
        header("location:" . "/inventory");
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

    public function accessRole(): array {
        return ['Administrator', 'Radnik'];
    }
}