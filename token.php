<?php
require 'jwt/jwt.php';

header('Content-Type: application/json');

$userId = 1;
echo json_encode(['token' => generateJWT($userId)]);
