<?php
// controllers/HomeController.php
namespace app\controllers;

use app\core\BaseController;

class HomeController extends BaseController {
    public function home() {
        $this->view->render('home', 'main', []);
    }

    public function accessRole(): array {
        return []; // Empty array means all roles have access
    }
}