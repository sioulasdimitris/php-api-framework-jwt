<?php

#Front Controller
#identify the request, identify the 3 elements(URL,ID,REQUEST METHOD) we need
#takes action based on URL,ID,REQUEST METHOD

declare(strict_types=1); #enable type declarations

require __DIR__ . "/bootstrap.php";

ini_set("display_errors", "On"); #

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); #removes the query string, holds the segment which identifies the resource

$parts = explode("/", $path);

$resource = $parts[2]; #3rd elements identifies the resource

$id = $parts[3] ?? null; #4th element identifies the id
$id = empty($id) ? null : $id;

if ($resource != "quotations") {
    http_response_code(404);
    exit;
}

#database configuration credentials
$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$user_gateway = new UserGateway($database);

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

$auth = new Auth($user_gateway, $codec);

if (!$auth->authenticateAccessToken()) {
    exit;
}

$user_id = $auth->getUserID();

$quotationGateway = new QuotationGateway($database);

$x = $_SERVER;

$controller = new QuotationController($quotationGateway, $user_id);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);

