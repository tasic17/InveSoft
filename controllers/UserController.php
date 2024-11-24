<?php
namespace app\controllers;

use app\core\Application;
use app\core\BaseController;
use app\models\UserModel;

class UserController extends BaseController {
    private function getStockActivityDetails(int $userId): array {
        $userModel = new UserModel();
        $query = "SELECT pz.*, p.naziv as proizvod
                 FROM promene_zaliha pz
                 JOIN proizvodi p ON pz.proizvodID = p.proizvodID
                 WHERE pz.korisnikID = ?
                 ORDER BY pz.datum_promene DESC";

        $stmt = $userModel->con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        return $activities;
    }

    public function readAll() {
        $userModel = new UserModel();
        $query = "SELECT k.korisnikID as id, k.ime as first_name, k.prezime as last_name, k.email, r.ime as role,
                        (SELECT COUNT(*) FROM promene_zaliha pz WHERE pz.korisnikID = k.korisnikID) as stock_changes,
                        (SELECT MAX(datum_promene) FROM promene_zaliha pz WHERE pz.korisnikID = k.korisnikID) as last_activity
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

        $userId = $_GET['id'];
        $userModel = new UserModel();

        // Check if target user is an administrator
        $checkQuery = "SELECT r.ime as role 
                      FROM korisnici k
                      JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                      JOIN role r ON kr.rolaID = r.rolaID
                      WHERE k.korisnikID = ?";

        $stmt = $userModel->con->prepare($checkQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $isTargetAdmin = false;
        while ($row = $result->fetch_assoc()) {
            if ($row['role'] === 'Administrator') {
                $isTargetAdmin = true;
                break;
            }
        }

        if ($isTargetAdmin && !$this->isAdmin()) {
            Application::$app->session->set('errorNotification', 'Nije moguće menjati podatke administratora!');
            header("location:" . "/users");
            exit;
        }

        // Get user data
        $query = "SELECT k.korisnikID as id, k.ime as first_name, k.prezime as last_name, k.email 
                 FROM korisnici k 
                 WHERE k.korisnikID = ?";

        $stmt = $userModel->con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userModel->mapData($result->fetch_assoc());
        }

        // Get stock activity
        $stockActivities = $this->getStockActivityDetails($userId);

        // Get all roles
        $roleQuery = "SELECT rolaID, ime FROM role";
        $roles = $userModel->executeQuery($roleQuery);

        // Get user's current role
        $userRoleQuery = "SELECT r.rolaID, r.ime 
                         FROM korisnici k
                         JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                         JOIN role r ON kr.rolaID = r.rolaID
                         WHERE k.korisnikID = ?";

        $stmt = $userModel->con->prepare($userRoleQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userRole = $stmt->get_result()->fetch_assoc();

        $viewData = [
            'model' => $userModel,
            'roles' => $roles,
            'userRole' => $userRole,
            'isAdmin' => $this->isAdmin(),
            'stockActivities' => $stockActivities
        ];

        $this->view->render('updateUser', 'main', $viewData);
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

        $formData = [
            'id' => $_POST['id'] ?? null,
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];

        // Check if target user is an administrator before processing the update
        $checkQuery = "SELECT r.ime as role 
                      FROM korisnici k
                      JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                      JOIN role r ON kr.rolaID = r.rolaID
                      WHERE k.korisnikID = ?";

        $stmt = $userModel->con->prepare($checkQuery);
        $stmt->bind_param("i", $formData['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($row['role'] === 'Administrator') {
                Application::$app->session->set('errorNotification', 'Nije moguće menjati podatke drugih administratora!');
                header("location:" . "/users");
                exit;
            }
        }

        $userModel->mapData($formData);

        if (!$userModel->validate()) {
            // Get roles for the form
            $roleQuery = "SELECT rolaID, ime FROM role";
            $roles = $userModel->executeQuery($roleQuery);

            $userRoleQuery = "SELECT r.rolaID, r.ime 
                             FROM korisnici k
                             JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                             JOIN role r ON kr.rolaID = r.rolaID
                             WHERE k.korisnikID = " . $formData['id'];
            $currentRole = $userModel->executeQuery($userRoleQuery)[0] ?? null;

            $viewData = [
                'model' => $userModel,
                'roles' => $roles,
                'userRole' => $currentRole,
                'isAdmin' => $this->isAdmin()
            ];

            Application::$app->session->set('errorNotification', 'Molimo popunite sva obavezna polja ispravno.');
            $this->view->render('updateUser', 'main', $viewData);
            return;
        }

        // Start transaction
        $userModel->con->begin_transaction();

        try {
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
                throw new \Exception($userModel->con->error);
            }

            // Update role if admin and role was changed
            $newRole = $_POST['role'] ?? null;
            if ($this->isAdmin() && $newRole) {
                $updateRoleQuery = "UPDATE korisnik_role SET rolaID = ? WHERE korisnikID = ?";
                $roleStmt = $userModel->con->prepare($updateRoleQuery);
                $roleStmt->bind_param("ii", $newRole, $userModel->id);

                if (!$roleStmt->execute()) {
                    throw new \Exception($userModel->con->error);
                }
            }

            $userModel->con->commit();
            Application::$app->session->set('successNotification', 'Korisnik je uspešno ažuriran!');
            header("location:" . "/users");
            exit;

        } catch (\Exception $e) {
            $userModel->con->rollback();

            $roleQuery = "SELECT rolaID, ime FROM role";
            $roles = $userModel->executeQuery($roleQuery);

            $userRoleQuery = "SELECT r.rolaID, r.ime 
                             FROM korisnici k
                             JOIN korisnik_role kr ON k.korisnikID = kr.korisnikID
                             JOIN role r ON kr.rolaID = r.rolaID
                             WHERE k.korisnikID = " . $formData['id'];
            $currentRole = $userModel->executeQuery($userRoleQuery)[0] ?? null;

            $viewData = [
                'model' => $userModel,
                'roles' => $roles,
                'userRole' => $currentRole,
                'isAdmin' => $this->isAdmin()
            ];

            Application::$app->session->set('errorNotification', 'Greška pri ažuriranju: ' . $e->getMessage());
            $this->view->render('updateUser', 'main', $viewData);
        }
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