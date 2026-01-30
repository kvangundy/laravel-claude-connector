<?php

namespace LaravelCloudConnector\Resources;

class EnvironmentResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getBranch(): ?string
    {
        return $this->getAttribute('branch');
    }

    public function getUrl(): ?string
    {
        return $this->getAttribute('url');
    }

    public function getPhpVersion(): ?string
    {
        return $this->getAttribute('php_version');
    }

    public function getNodeVersion(): ?string
    {
        return $this->getAttribute('node_version');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function isRunning(): bool
    {
        return $this->getStatus() === 'running';
    }

    public function isStopped(): bool
    {
        return $this->getStatus() === 'stopped';
    }

    public function getApplication(): ?ApplicationResource
    {
        $relationship = $this->getRelationship('application');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        $included = $this->findIncluded('applications', $relationship['data']['id']);
        return $included ? new ApplicationResource($included, $this->included) : null;
    }

    public function getInstances(): array
    {
        $relationship = $this->getRelationship('instances');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $instances = [];
        foreach ($relationship['data'] as $instance) {
            $included = $this->findIncluded('instances', $instance['id']);
            if ($included) {
                $instances[] = new InstanceResource($included, $this->included);
            }
        }

        return $instances;
    }

    public function getDomains(): array
    {
        $relationship = $this->getRelationship('domains');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $domains = [];
        foreach ($relationship['data'] as $domain) {
            $included = $this->findIncluded('domains', $domain['id']);
            if ($included) {
                $domains[] = new DomainResource($included, $this->included);
            }
        }

        return $domains;
    }
}
