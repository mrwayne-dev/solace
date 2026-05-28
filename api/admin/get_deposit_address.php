<?php
require_once("../../config/database.php");
session_start();

if(!isset($_SESSION["admin_id"])){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT cash_mailing_address, wallet_deposit_address FROM settings WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => [
            "cash_mailing" => $row["cash_mailing_address"] ?? null,
            "wallet_address" => $row["wallet_deposit_address"] ?? null
        ]
    ]);

} catch(Exception $e){
    echo json_encode(["status"=>"error","message"=>"Server error"]);
}
