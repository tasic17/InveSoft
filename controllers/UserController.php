<?php
namespace app\controllers;

use app\core\Application;
use app\core\BaseController;
use app\models\UserModel;

class UserController extends BaseController {
    public function readAll() {
        $userModel = new UserModel();
        $query = "SELECT k.korisnikID as id, k.ime as first_name, k.prezime as last_name, k.email, r.ime as role 
                 FROM korisnici k
                 JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                 JOIN role r ON kr.rolaID = r.rolaID
                 ORDER BY k.korisnikID";

        $results = $userModel->executeQuery($query);
        $this->view->render('users', 'main', $results);
    }

    public function updateUser() {
        if (!isset($_GET['id'])) {
            header("location:" . "/users");
            exit;
        }

        $userModel = new UserModel();
        $query = "SELECT k.korisnikID as id, k.ime as first_name, k.prezime as last_name, k.email 
                 FROM korisnici k 
                 WHERE k.korisnikID = " . $_GET['id'];

        $result = $userModel->executeQuery($query);

        if (!empty($result)) {
            $userModel->mapData($result[0]);
        }

        $this->view->render('updateUser', 'main', $userModel);
    }

    public function createUser() {
        $userModel = new UserModel();
        $this->view->render('createUser', 'main', $userModel);
    }

    public function deleteUser() {
        if (!isset($_GET['id'])) {
            Application::$app->session->set('errorNotification', 'ID korisnika nije prosleđen!');
            header("location:" . "/users");
            exit;
        }

        $userModel = new UserModel();
        $userId = $_GET['id'];

        // First check if user is an administrator
        $checkQuery = "SELECT r.ime as role 
                      FROM korisnici k
                      JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                      JOIN role r ON kr.rolaID = r.rolaID
                      WHERE k.korisnikID = ?";

        $stmt = $userModel->con->prepare($checkQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($row['role'] === 'Administrator') {
                Application::$app->session->set('errorNotification', 'Nije moguće obrisati administratora!');
                header("location:" . "/users");
                exit;
            }
        }

        // Start transaction
        $userModel->con->begin_transaction();

        try {
            // Delete from korisnik_role first (foreign key constraint)
            $deleteRoleQuery = "DELETE FROM korisnik_role WHERE korisnikID = ?";
            $stmt = $userModel->con->prepare($deleteRoleQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            // Then delete from korisnici
            $deleteUserQuery = "DELETE FROM korisnici WHERE korisnikID = ?";
            $stmt = $userModel->con->prepare($deleteUserQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            // Commit transaction
            $userModel->con->commit();

            Application::$app->session->set('successNotification', 'Korisnik uspešno obrisan!');
        } catch (\Exception $e) {
            // Rollback in case of error
            $userModel->con->rollback();
            Application::$app->session->set('errorNotification', 'Greška pri brisanju korisnika: ' . $e->getMessage());
        }

        header("location:" . "/users");
    }

    public function processUpdateUser() {
        $userModel = new UserModel();
        $userModel->mapData($_POST);
        $userModel->validate();

        if ($userModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri ažuriranju korisnika!');
            $this->view->render('updateUser', 'main', $userModel);
            exit;
        }

        $query = "UPDATE korisnici SET 
                 ime = ?, 
                 prezime = ?, 
                 email = ? 
                 WHERE korisnikID = ?";

        $stmt = $userModel->con->prepare($query);
        $stmt->bind_param("sssi",
            $userModel->first_name,
            $userModel->last_name,
            $userModel->email,
            $userModel->id
        );

        if (!$stmt->execute()) {
            Application::$app->session->set('errorNotification', 'Greška pri ažuriranju korisnika: ' . $userModel->con->error);
            $this->view->render('updateUser', 'main', $userModel);
            exit;
        }

        Application::$app->session->set('successNotification', 'Korisnik uspešno ažuriran!');
        header("location:" . "/users");
    }

    public function processCreate() {
        $userModel = new UserModel();
        $userModel->mapData($_POST);
        $userModel->validate();

        if ($userModel->errors) {
            Application::$app->session->set('errorNotification', 'Greška pri kreiranju korisnika!');
            $this->view->render('createUser', 'main', $userModel);
            exit;
        }

        // Hash the password
        $userModel->password = password_hash($userModel->password, PASSWORD_DEFAULT);

        // Start transaction
        $userModel->con->begin_transaction();

        try {
            // Prepare the query
            $query = "INSERT INTO korisnici (ime, prezime, email, password) 
                     VALUES (?, ?, ?, ?)";

            $stmt = $userModel->con->prepare($query);
            $stmt->bind_param("ssss",
                $userModel->first_name,
                $userModel->last_name,
                $userModel->email,
                $userModel->password
            );

            if (!$stmt->execute()) {
                throw new \Exception($userModel->con->error);
            }

            $userId = $userModel->con->insert_id;

            // Assign default role (2 for 'Radnik')
            $roleQuery = "INSERT INTO korisnik_role (korisnikID, rolaID) VALUES (?, 2)";
            $roleStmt = $userModel->con->prepare($roleQuery);
            $roleStmt->bind_param("i", $userId);

            if (!$roleStmt->execute()) {
                throw new \Exception($userModel->con->error);
            }

            $userModel->con->commit();
            Application::$app->session->set('successNotification', 'Korisnik uspešno kreiran!');
            header("location:" . "/users");
        } catch (\Exception $e) {
            $userModel->con->rollback();
            Application::$app->session->set('errorNotification', 'Greška pri kreiranju korisnika: ' . $e->getMessage());
            $this->view->render('createUser', 'main', $userModel);
            exit;
        }
    }

    public function accessRole(): array {
        return ['Administrator'];
    }
}