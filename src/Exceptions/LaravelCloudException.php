<?php

namespace LaravelCloudConnector\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class LaravelCloudException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?Response $response = null,
        public readonly ?array $errors = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $message = $body['message'] ?? 'An error occurred with the Laravel Cloud API';
        $errors = $body['errors'] ?? null;

        return new self(
            message: $message,
            code: $response->status(),
            response: $response,
            errors: $errors,
        );
    }

    public function getValidationErrors(): ?array
    {
        return $this->errors;
    }
}
