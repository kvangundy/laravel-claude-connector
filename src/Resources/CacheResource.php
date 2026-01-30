<?php

namespace LaravelCloudConnector\Resources;

class CacheResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getSize(): ?string
    {
        return $this->getAttribute('size');
    }

    public function getRegion(): ?string
    {
        return $this->getAttribute('region');
    }

    public function getHost(): ?string
    {
        return $this->getAttribute('host');
    }

    public function getPort(): ?int
    {
        return $this->getAttribute('port');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function isRunning(): bool
    {
        return $this->getStatus() === 'running';
    }
}
