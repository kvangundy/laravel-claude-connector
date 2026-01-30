<?php

namespace LaravelCloudConnector\Resources;

class DatabaseClusterResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getEngine(): ?string
    {
        return $this->getAttribute('engine');
    }

    public function getEngineVersion(): ?string
    {
        return $this->getAttribute('engine_version');
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

    public function getDatabases(): array
    {
        $relationship = $this->getRelationship('databases');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $databases = [];
        foreach ($relationship['data'] as $database) {
            $included = $this->findIncluded('databases', $database['id']);
            if ($included) {
                $databases[] = new DatabaseResource($included, $this->included);
            }
        }

        return $databases;
    }
}
