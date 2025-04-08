<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$SECRET_KEY = "bwedfmobnefrbnqsdjbkqwfebnasdfcn";

function generateJWT($userId) {
    global $SECRET_KEY;
    $payload = [
        'iss' => 'localhost',
        'iat' => time(),
        'exp' => time() + (60 * 60),
        'uid' => $userId
    ];
    return JWT::encode($payload, $SECRET_KEY, 'HS256');
}

function verifyJWT($token) {
    global $SECRET_KEY;
    try {
        return JWT::decode($token, new Key($SECRET_KEY, 'HS256'));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: ' . $e->getMessage()]);
    }
}
