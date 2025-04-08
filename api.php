<?php
require 'database/db.php';
require 'jwt/jwt.php';

header('Content-Type: application/json');

// JWT token check
$headers = apache_request_headers();
$bearer = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

if (!str_starts_with($bearer, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
}

$token = substr($bearer, 7);
$decoded = verifyJWT($token);

// Routing
$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

if (($key = array_search('api.php', $uri)) !== false) {
    $uri = array_slice($uri, $key + 1);
}

$resource = $uri[0] ?? null;
$id = $uri[1] ?? null;

$input = json_decode(file_get_contents('php://input'), true);


if ($resource !== 'products') {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
 
}

switch ($method) {
    case 'POST':
        if (!isset($input['name'], $input['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing name or price']);
        }

        if(!is_numeric($input['price'])){
            http_response_code(400);
            echo json_encode(['error' => 'Price must be a number']);
        }
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$input['name'], $input['price'], $input['description'] ?? null]);
        echo json_encode(['message' => 'Product created']);
        break;

    case 'GET':
        if ($id && is_numeric($id)) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                echo json_encode($product);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'PUT':
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID']);
            
        }
        if (!isset($input['name'], $input['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing name or price']);
           
        }
        if(!is_numeric($input['price'])){
            http_response_code(400);
            echo json_encode(['error' => 'Price must be a number']);
        }

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            break;
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?");
        $stmt->execute([$input['name'], $input['price'], $input['description'] ?? null, $id]);
        echo json_encode(['message' => 'Product updated']);
        break;

    case 'DELETE':
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID']);
            
        }
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['message' => 'Product deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
