<?php

#NOT RESTFUL endpoint

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

#restrict methods to call this endpoint
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit;
}

$data = (array)json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("token", $data)) {
    http_response_code(400);
    echo json_encode(["message" => "missing token"]);
    exit;
}

#once we have the token value we need to decode it
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

try {
    $payload = $codec->decode($data["token"]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["message" => "invalid token"]);
    exit;
}

$user_id = $payload["sub"];

$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

#check if the token is on the whitelist
$refresh_token = $refresh_token_gateway->getByToken($data["token"]);

if ($refresh_token === false) {
    http_response_code(400);
    echo json_encode(["message" => "invalid token (not on whitelist)"]);
    exit;
}

$user_gateway = new UserGateway($database);

#if no record is found with that id the method will return false
#we are getting the user record from the database based on the id in the refresh token
$user = $user_gateway->getByID($user_id);

if ($user === false) {
    http_response_code(401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

#to keep it simple if the user exists in the db he is authorized to access the API
#after the authentication of the user we issue a new access token and a new refresh token

require __DIR__ . "/tokens.php";

#before we insert a new record we delete the previous token
#we need one valid token at a time per user
$refresh_token_gateway->delete($data["token"]);

$refresh_token_gateway->create($refresh_token, $refresh_token_expiry);