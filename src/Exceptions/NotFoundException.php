<?php

namespace LaravelCloudConnector\Exceptions;

class NotFoundException extends LaravelCloudException
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message, 404);
    }
}
