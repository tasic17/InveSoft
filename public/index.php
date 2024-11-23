<?php

require_once __DIR__ . "/../vendor/autoload.php";

use app\controllers\AuthController;
use app\controllers\HomeController;
use app\controllers\InventoryController;
use app\controllers\UserController;
use app\core\Application;

$app = new Application();

// Auth Routes
$app->router->get("/login", [AuthController::class, 'login']);
$app->router->get("/registration", [AuthController::class, 'registration']);
$app->router->post("/processRegistration", [AuthController::class, 'processRegistration']);
$app->router->post("/processLogin", [AuthController::class, 'processLogin']);
$app->router->get("/processLogout", [AuthController::class, 'processLogout']);
$app->router->get("/accessDenied", [AuthController::class, 'accessDenied']);

// Home Routes
$app->router->get("/", [HomeController::class, 'home']);

// User Routes
$app->router->get("/users", [UserController::class, 'readAll']);
$app->router->get("/updateUser", [UserController::class, 'updateUser']);
$app->router->get("/createUser", [UserController::class, 'createUser']);
$app->router->post("/processUpdateUser", [UserController::class, 'processUpdateUser']);
$app->router->post("/processCreateUser", [UserController::class, 'processCreate']);
$app->router->get("/deleteUser", [UserController::class, 'deleteUser']);

// Inventory Routes
$app->router->get("/inventory", [InventoryController::class, 'overview']);
$app->router->get("/inventory/add-product", [InventoryController::class, 'addProduct']);
$app->router->post("/inventory/process-add-product", [InventoryController::class, 'processAddProduct']);
$app->router->get("/inventory/update-stock", [InventoryController::class, 'updateStock']);
$app->router->post("/inventory/process-update-stock", [InventoryController::class, 'processUpdateStock']);
$app->router->get("/inventory/history", [InventoryController::class, 'stockHistory']);

$app->run();