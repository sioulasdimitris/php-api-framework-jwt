<?php

$payload = [
    "sub" => $user["id"],
    "name" => $user["name"],
    "exp" => time() + 300 # 300 for 5 min expiry
];

#generate a JWT access token
$access_token = $codec->encode($payload);

$refresh_token_expiry = time() + 432000; # 432000 for 5 days expiry

#generate a JWT refresh token
$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => $refresh_token_expiry
]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token
]);