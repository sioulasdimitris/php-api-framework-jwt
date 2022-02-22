<?php

require __DIR__. "/vendor/autoload.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

    $conn = $database->getConnection();

    $sql = "INSERT INTO user (name, username, password_hash, api_key)
            VALUES (:name, :username, :password_hash, :api_key)";

    $stmt = $conn->prepare($sql);

    #hash password
    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

    #generate a key of random bytes ans convert it into a sting
    $api_key = bin2hex(random_bytes(16));

    $stmt->bindValue(":name", $_POST["name"]);
    $stmt->bindValue(":username", $_POST["username"]);
    $stmt->bindValue(":password_hash", $password_hash);
    $stmt->bindValue(":api_key", $api_key);

    $stmt->execute();

    echo "Thank you for registering. Your API key is: ", $api_key;
    exit;

}


?>

<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">

</head>
<body>

<main class="container">

    <h1>Register</h1>

    <form method="post">

        <label for="name">
            Name
            <input name="name" id="name">
        </label>

        <label for="username">
            Username
            <input name="username" id="username">
        </label>

        <label for="password">
            Password
            <input name="password" id="password">
        </label>

        <button>Register</button>

    </form>
</main>

</body>
</html>