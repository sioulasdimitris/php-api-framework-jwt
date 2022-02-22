<?php

class QuotationGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * From quotation ORDER BY id";

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllForUser(int $user_id)
    {
        $sql = "SELECT * 
                From quotation 
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForUser(int $user_id, string $id)
    {
        $sql = "SELECT *
            FROM quotation
            WHERE id =:id
            AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function createForUser(int $user_id, array $data): string
    {
        $sql = "INSERT INTO quotation (age, start_date, end_date, total, currency_id,user_id)
            VALUES(:age, :start_date, :end_date,:total,:currency_id,:user_id)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":age", $data["age"]);
        $stmt->bindValue(":start_date", $data["start_date"]);
        $stmt->bindValue(":end_date", $data["end_date"]);
        $stmt->bindValue(":total", Helper::calculateTotal($data["age"], $data["start_date"], $data["end_date"]));
        $stmt->bindValue(":currency_id", $data["currency_id"] );
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    public function updateForUser(int $user_id, string $id, array $data): int
    {
        $fields = [];

        if (array_key_exists("age", $data)) {
            $fields["age"] = [
                $data["age"],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists("start_date", $data)) {
            $fields["start_date"] = [
                $data["start_date"],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists("end_date", $data)) {
            $fields["end_date"] = [
                $data["end_date"],
                PDO::PARAM_STR
            ];
        }

        if (array_key_exists("total", $data)) {
            $fields["total"] = [
                $data["total"],
                PDO::PARAM_STR
            ];
        }

        if (empty($fields)) {

            #if there are no fields to update
            return 0;

        } else {

            #an array of strings with the sql set statements
            $sets = array_map(function ($value) {
                return "$value = :$value";
            }, array_keys($fields));

            $sql = "UPDATE quotation"
                . " SET " . implode(", ", $sets)
                . " WHERE id = :id"
                . " AND user_id = :user_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

            foreach ($fields as $name => $values) {
                $stmt->bindValue(":$name", $values[0], $values[1]);
            }

            $stmt->execute();

            return $stmt->rowCount();

        }

    }

    public function deleteForUser(int $user_id, string $id): int
    {
        $sql = "DELETE FROM quotation WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

}