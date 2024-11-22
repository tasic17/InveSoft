<?php
namespace app\controllers;

use app\core\Application;
use app\core\BaseController;
use app\models\LoginModel;
use app\models\RegistrationModel;

class AuthController extends BaseController {
    public function login() {
        if (Application::$app->session->get('user')) {
            header("location:" . "/");
            exit;
        }

        $loginModel = new LoginModel();
        $this->view->render('login', 'auth', $loginModel);
    }

    public function registration() {
        if (Application::$app->session->get('user')) {
            header("location:" . "/");
            exit;
        }

        $registrationModel = new RegistrationModel();
        $this->view->render('registration', 'auth', $registrationModel);
    }

    public function processRegistration() {
        $registrationModel = new RegistrationModel();
        $registrationModel->mapData($_POST);
        $registrationModel->validate();

        if ($registrationModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri registraciji!');
            $this->view->render('registration', 'auth', $registrationModel);
            exit;
        }

        $registrationModel->password = password_hash($registrationModel->password, PASSWORD_DEFAULT);

        // Prepare the query using prepared statements
        $query = "INSERT INTO korisnici (ime, prezime, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $registrationModel->con->prepare($query);
        $stmt->bind_param("ssss",
            $registrationModel->ime,
            $registrationModel->prezime,
            $registrationModel->email,
            $registrationModel->password
        );

        if (!$stmt->execute()) {
            Application::$app->session->set('errorNotification', 'Greška pri registraciji: ' . $registrationModel->con->error);
            $this->view->render('registration', 'auth', $registrationModel);
            exit;
        }

        $userId = $registrationModel->con->insert_id;

        // Insert default role (2 for 'Radnik')
        $roleQuery = "INSERT INTO korisnik_role (korisnikID, rolaID) VALUES (?, 2)";
        $roleStmt = $registrationModel->con->prepare($roleQuery);
        $roleStmt->bind_param("i", $userId);
        $roleStmt->execute();

        Application::$app->session->set('successNotification', 'Uspešno ste se registrovali!');
        header("location:" . "/login");
    }

    public function processLogin() {
        $loginModel = new LoginModel();
        $loginModel->mapData($_POST);
        $loginModel->validate();

        if ($loginModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri prijavljivanju!');
            $this->view->render('login', 'auth', $loginModel);
            exit;
        }

        // First, get the user's data including password
        $baseQuery = "SELECT * FROM korisnici WHERE email = ?";
        $stmt = $loginModel->con->prepare($baseQuery);
        $stmt->bind_param("s", $loginModel->email);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        if (!$userData || !password_verify($loginModel->password, $userData['password'])) {
            Application::$app->session->set('errorNotification', 'Pogrešni kredencijali!');
            $this->view->render('login', 'auth', $loginModel);
            exit;
        }

        // If credentials are correct, get user data with roles
        $roleQuery = "SELECT k.korisnikID, k.ime, k.prezime, k.email, r.ime as role 
                     FROM korisnici k 
                     JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID 
                     JOIN role r ON kr.rolaID = r.rolaID 
                     WHERE k.email = ?";

        $stmt = $loginModel->con->prepare($roleQuery);
        $stmt->bind_param("s", $loginModel->email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = [];
        while ($row = $result->fetch_assoc()) {
            $user[] = [
                'id' => $row['korisnikID'],
                'first_name' => $row['ime'],
                'last_name' => $row['prezime'],
                'email' => $row['email'],
                'role' => $row['role']
            ];
        }

        if (empty($user)) {
            Application::$app->session->set('errorNotification', 'Greška pri učitavanju korisničkih podataka!');
            $this->view->render('login', 'auth', $loginModel);
            exit;
        }

        Application::$app->session->set('user', $user);
        Application::$app->session->set('successNotification', 'Uspešno ste se prijavili!');
        header("location:" . "/");
    }

    public function processLogout() {
        Application::$app->session->delete('user');
        Application::$app->session->set('successNotification', 'Uspešno ste se odjavili!');
        header("location:" . "/login");
    }

    public function accessDenied() {
        $this->view->render('accessDenied', 'auth', null);
    }

    public function accessRole(): array {
        return []; // Empty array means all roles have access
    }
}