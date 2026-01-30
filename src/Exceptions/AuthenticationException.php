<?php

namespace LaravelCloudConnector\Exceptions;

class AuthenticationException extends LaravelCloudException
{
    public function __construct(string $message = 'Invalid or missing API token')
    {
        parent::__construct($message, 401);
    }
}
