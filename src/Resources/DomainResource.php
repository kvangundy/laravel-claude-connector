<?php

namespace LaravelCloudConnector\Resources;

class DomainResource extends Resource
{
    public function getDomain(): ?string
    {
        return $this->getAttribute('domain');
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function isVerified(): bool
    {
        return $this->getAttribute('verified', false);
    }

    public function getDnsRecords(): ?array
    {
        return $this->getAttribute('dns_records');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function getEnvironment(): ?EnvironmentResource
    {
        $relationship = $this->getRelationship('environment');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        $included = $this->findIncluded('environments', $relationship['data']['id']);
        return $included ? new EnvironmentResource($included, $this->included) : null;
    }
}
