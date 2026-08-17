<?php
    header('Content-Type: application/json');

    $host = '127.0.0.1';  // to be replace by proper hosting sites
    $database = 'lyfepharmacydb';
    
    // user credential
    $user = 'root';  // to be replace by user;
    $pass = '';
    
    $dsn = "mysql:host=$host; dbname = $database; charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database connection Failed."]);
        exit;
    }

    $usernameInput = $_POST['username'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $roleInput     = $_POST['role'] ?? '';

    if(empty($usernameInput) || empty($passwordInput) || empty($roleInput)){
        echo json_encode(["success" => false, "message" => "Please enter username and password."]);
        exit;
    }

    $sql = 'SELECT u.user_id, u.first_name, u.last_name, u.password, u.status, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.username = ? AND r.role_name = ?';

    $stmt = $pdo->prepare($sql);
    $stmt -> execute([$usernameInput, $roleInput]);
    $userRecord = $stmt->fetch();

    if (!$userRecord){
        echo json_encode(["success" => false, "message" => "Invalid username or role authorization."]);
    }

    // Note: This checks raw passwords for development. 
    // In production, always use password_hash() when saving and password_verify() here.
    if ($passwordInput !== $userRecord['password']) {
        echo json_encode(["success" => false, "message" => "Invalid password."]);
        exit;
    }

    if (strtolower($userRecord['status']) === 'inactive' || strtolower($userRecord['status']) === 'pending') {
        echo json_encode(["success" => false, "message" => "Account is disabled or pending approval. Contact the Owner."]);
        exit;
    }

    echo json_encode([
        "success" => true, 
        "user" => [
            "id" => $userRecord['user_id'],
            "name" => trim($userRecord['first_name'] . ' ' . $userRecord['last_name']),
            "role" => $userRecord['role_name']
        ]
    ]);
?>