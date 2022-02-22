<?php

class Auth
{
    private UserGateway $user_gateway;
    private int $user_id;
    private JWTCodec $codec;

    public function __construct(UserGateway $user_gateway, JWTCodec $codec)
    {
        $this->user_gateway = $user_gateway;
        $this->codec = $codec;
    }

    public function authenticateAPIKey(): bool
    {
        if (empty($_SERVER["HTTP_X_API_KEY"])) { #check if empty array or not given at all
            http_response_code(400);
            echo json_encode(["message" => "missing API key"]);
            return false;
        }

        #HTTP_X_API_KEY request header for authentication
        $api_key = $_SERVER["HTTP_X_API_KEY"];

        $user = $this->user_gateway->getByApiKey($api_key);

        if ($user === false) {
            http_response_code(401);
            echo json_encode(["message" => "invalid API key"]);
            return false;
        }

        $this->user_id = $user["id"];

        return true;

    }

    public function getUserID(): int
    {
        return $this->user_id;
    }

    public function authenticateAccessToken(): bool
    {
        #the value of the Authorization header check it its null
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        #check if the regular expression matches the header value the second argument $matches will contain the actual value
        #Bearer is an authentication scheme which goes together with the HTTP_AUTHORIZATION HEADER
        if (!preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        try {
            $data = $this->codec->decode($matches[1]); #returns the payload which in this case is the user data
        } catch (InvalidSignatureException $e) {
            http_response_code(401);
            echo json_encode(["message" => "invalid signature"]);
            return false;
        } catch (TokenExpiredException $e) {
            http_response_code(401);
            echo json_encode(["message" => "token has expired"]);
            return false;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => $e->getMessage()]);
            return false;
        }

        $this->user_id = $data["sub"];

        return true;

    }


}