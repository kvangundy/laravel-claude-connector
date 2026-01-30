<?php

namespace LaravelCloudConnector\Resources;

class BackgroundProcessResource extends Resource
{
    public const TYPE_WORKER = 'worker';
    public const TYPE_DAEMON = 'daemon';

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getProcessType(): ?string
    {
        return $this->getAttribute('type');
    }

    public function getCommand(): ?string
    {
        return $this->getAttribute('command');
    }

    public function getProcessCount(): ?int
    {
        return $this->getAttribute('process_count');
    }

    public function getQueue(): ?string
    {
        return $this->getAttribute('queue');
    }

    public function getConnection(): ?string
    {
        return $this->getAttribute('connection');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function isWorker(): bool
    {
        return $this->getProcessType() === self::TYPE_WORKER;
    }

    public function isDaemon(): bool
    {
        return $this->getProcessType() === self::TYPE_DAEMON;
    }

    public function getInstance(): ?InstanceResource
    {
        $relationship = $this->getRelationship('instance');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        $included = $this->findIncluded('instances', $relationship['data']['id']);
        return $included ? new InstanceResource($included, $this->included) : null;
    }
}
