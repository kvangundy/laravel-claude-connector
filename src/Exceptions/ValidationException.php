<?php

namespace LaravelCloudConnector\Exceptions;

use Illuminate\Http\Client\Response;

class ValidationException extends LaravelCloudException
{
    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $message = $body['message'] ?? 'Validation failed';
        $errors = $body['errors'] ?? null;

        return new self(
            message: $message,
            code: 422,
            response: $response,
            errors: $errors,
        );
    }
}
