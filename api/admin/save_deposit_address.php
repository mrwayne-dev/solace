<?php
// FILE: /api/admin/save_deposit_address.php
// PURPOSE: Updates a specific deposit method (cash_mailing_address or wallet_deposit_address)
// in the single-row 'settings' table.

require_once("../../config/database.php");
session_start();

// ---------------------------
// 1. ADMIN AUTH CHECK
// ---------------------------
if(!isset($_SESSION["admin_id"])){
    http_response_code(401); // Unauthorized
    echo json_encode(["status"=>"error","message"=>"Unauthorized access"]);
    exit;
}

// ---------------------------
// 2. INPUT VALIDATION
// ---------------------------
$data = json_decode(file_get_contents("php://input"), true);
$method = $data["method"] ?? null;
$value = trim($data["value"] ?? "");

// Basic validation for required fields
if(!$method || $value === ""){
    http_response_code(400); // Bad Request
    echo json_encode(["status"=>"error","message"=>"Deposit method and value are required."]);
    exit;
}

// ---------------------------
// 3. DATABASE OPERATION (UPSERT)
// ---------------------------
try {
    $pdo = getPDO();
    
    // Determine the corresponding column name
    $column = '';
    $message_key = '';

    if($method === "cash_mailing"){
        $column = 'cash_mailing_address';
        $message_key = 'Cash Mailing Address';
    } elseif($method === "wallet_address"){
        // Additional check for crypto/wallet format might be added here in a real app
        $column = 'wallet_deposit_address';
        $message_key = 'Wallet Deposit Address';
    } else {
        http_response_code(400);
        echo json_encode(["status"=>"error","message"=>"Invalid deposit method specified."]);
        exit;
    }

    // Use INSERT... ON DUPLICATE KEY UPDATE (UPSERT)
    // The settings table should only have one row with id=1. 
    // This handles both the initial creation of the row (if id=1 doesn't exist) 
    // and subsequent updates.
    $sql = "INSERT INTO settings (id, $column) VALUES (1, :value)
            ON DUPLICATE KEY UPDATE $column = :value";
            
    $stmt = $pdo->prepare($sql);
    
    // Bind the value parameter. The column name cannot be bound as a parameter in this way.
    $stmt->execute([':value' => $value]);

    // Although rowCount() returns 1 for INSERT and 2 for UPDATE, 
    // checking for successful execution is sufficient.
    echo json_encode(["status"=>"success","message"=> $message_key . " updated successfully."]);
    
} catch(Exception $e){
    // Log the error for internal review
    error_log("Database Error in save_deposit_address.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>"A server error occurred during the update process."]);
}
?>