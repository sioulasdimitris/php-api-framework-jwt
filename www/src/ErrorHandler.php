<?php

class ErrorHandler
{

    /**
     * @throws ErrorException
     */
    public static function handleError(int $errno, string $errstr, string $errrfile, int $errline): void
    {
        throw new ErrorException($errstr, 0, $errno, $errrfile, $errline);
    }


    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        echo json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
}