<?php
namespace app\controllers;

use app\core\Application;
use app\core\BaseController;

class HomeController extends BaseController {
    public function home() {
        $user = Application::$app->session->get('user');
        if ($user) {
            $role = $user[0]['role'];
            if ($role === 'Administrator') {
                header("location:" . "/users");
            } else {
                header("location:" . "/inventory");
            }
            exit;
        }

        // If not logged in, redirect to login
        header("location:" . "/login");
        exit;
    }

    public function accessRole(): array {
        return []; // Empty array means all roles have access
    }
}