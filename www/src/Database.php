<?php

class Database
{
    #cache the database connection
    private ?PDO $conn = null;

    private string $host;
    private string $name;
    private string $user;
    private string $password;

    public function __construct(string $host, string $name, string $user, string $password)
    {
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->password = $password;
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null) { #avoid multiple database connections
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

            return new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, #PDO throws an exception when a database error occurs
                PDO::ATTR_EMULATE_PREPARES => false, # prevent from converting the numeric values into Strings
                PDO::ATTR_STRINGIFY_FETCHES => false, # >> >>
            ]);
        }
        return $this->conn;
    }
}