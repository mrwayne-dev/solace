<?php
require_once("../../config/database.php");
session_start();

if(!isset($_SESSION["admin_id"])){
    echo json_encode(["status"=>"error","message"=>"Unauthorized access"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$method = $data["method"] ?? null;
$value = trim($data["value"] ?? "");

if(!$method || !$value){
    echo json_encode(["status"=>"error","message"=>"Invalid input"]);
    exit;
}

try {
    $pdo = getPDO();

    if($method === "cash_mailing"){
        $stmt = $pdo->prepare("UPDATE wallets SET cash_mailing_address = :v");
    } elseif($method === "wallet_address"){
        $stmt = $pdo->prepare("UPDATE wallets SET wallet_deposit_address = :v");
    } else {
        echo json_encode(["status"=>"error","message"=>"Invalid method"]);
        exit;
    }

    $stmt->execute([":v" => $value]);

    echo json_encode(["status"=>"success","message"=>"Deposit address updated successfully"]);
    
} catch(Exception $e){
    echo json_encode(["status"=>"error","message"=>"Server error"]);
}
