<?php

#controller for the interaction with quotation resources
#controller decides what to do with based on the requested method and if there is an id in the URL or not

class QuotationController
{
    private QuotationGateway $gateway;
    private int $user_id;

    public function __construct(QuotationGateway $gateway, int $user_id)
    {
        $this->gateway = $gateway;
        $this->user_id = $user_id;
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") { #list of all quotation records
                echo json_encode($this->gateway->getAllForUser($this->user_id));
            } elseif ($method == "POST") {
                $requestBody = file_get_contents("php://input"); #receive the data from the request body
                $data = (array)json_decode($requestBody, true);
                $errors = $this->getValidationErrors($data); #validate request data
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }
                $id = $this->gateway->createForUser($this->user_id, $data);
                $this->respondCreated($id);
            } else {
                $this->responseMethodNotAllowed("GET, POST"); #unsupported methods
            }
        } else {

            #check if the requested id exists
            $quotation = $this->gateway->getForUser($this->user_id, $id);
            if ($quotation === false) {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                case "GET":
                    echo json_encode($quotation);
                    break;
                case "PATCH":

                    $requestBody = file_get_contents("php://input"); #receive the data from the request body
                    $data = (array)json_decode($requestBody, true);
                    $errors = $this->getValidationErrors($data, false); #validate request data
                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $rows = $this->gateway->updateForUser($this->user_id, $id, $data);
                    echo json_encode(["message" => "Quotation Updated", "row" => $rows]);
                    break;
                case "DELETE":
                    $rows = $this->gateway->deleteForUser($this->user_id, $id);
                    echo json_encode(["message" => "Quotation Deleted", "row" => $rows]);

                    break;
                default:
                    $this->responseMethodNotAllowed("GET, PATCH, DELETE"); #unsupported methods
            }
        }
    }

    private function responseMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Quotation with ID $id not found"]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["message" => "Quotation created", "id" => $id]);

    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    #simple validation on request body
    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];


        if ($is_new) { #validation for creating a new record
            if (empty($data["age"])) {
                $errors[] = "age is required";
            } else {
                $age = Helper::formatAge($data["age"]);
                $age = Helper::isAgeValid($age);
                if ($age === false) {
                    $errors[] = "invalid age format";
                }
            }

            if (empty($data["start_date"])) {
                $errors[] = "start_date is required";
            } else {
                if (!Helper::isDateValid($data["start_date"])) {
                    $errors[] = "invalid start_date format";
                }
            }

            if (empty($data["end_date"])) {
                $errors[] = "end_date is required";
            } else {
                if (!Helper::isDateValid($data["end_date"])) {
                    $errors[] = "invalid end_date format";
                }
            }

            if (empty($data["currency_id"])) {
                $errors[] = "currency_id is required";
            } else {
                if (!Helper::isCurrencyValid($data["currency_id"])) {
                    $errors[] = "invalid currency format";
                }
            }
        } else { #validation for updating an existing record
            if (array_key_exists("age", $data)) {
                if (empty($data["age"])) {
                    $errors[] = "age is required";
                } else {
                    $age = Helper::formatAge($data["age"]);
                    $age = Helper::isAgeValid($age);
                    if ($age === false) {
                        $errors[] = "invalid age format";
                    }
                }
            }
            if (array_key_exists("start_date", $data)) {
                if (empty($data["start_date"])) {
                    $errors[] = "start_date is required";
                } else {
                    if (!Helper::isDateValid($data["start_date"])) {
                        $errors[] = "invalid start_date format";
                    }
                }
            }
            if (array_key_exists("end_date", $data)) {
                if (empty($data["end_date"])) {
                    $errors[] = "end_date is required";
                } else {
                    if (!Helper::isDateValid($data["end_date"])) {
                        $errors[] = "invalid end_date format";
                    }
                }
            }
            if (array_key_exists("currency_id", $data)) {
                if (empty($data["currency_id"])) {
                    $errors[] = "currency_id is required";
                } else {
                    if (filter_var($data["currency_id"], FILTER_VALIDATE_INT) === false) {
                        $errors[] = "currency_id must be an integer";
                    }
                }
                #check valid currency_id
            }
        }

        return $errors;
    }


}