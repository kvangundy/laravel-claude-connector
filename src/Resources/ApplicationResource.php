<?php

namespace LaravelCloudConnector\Resources;

class ApplicationResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getSlug(): ?string
    {
        return $this->getAttribute('slug');
    }

    public function getRegion(): ?string
    {
        return $this->getAttribute('region');
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getAttribute('avatar_url');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getRepository(): ?array
    {
        $relationship = $this->getRelationship('repository');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        return $this->findIncluded('repositories', $relationship['data']['id']);
    }

    public function getOrganization(): ?array
    {
        $relationship = $this->getRelationship('organization');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        return $this->findIncluded('organizations', $relationship['data']['id']);
    }

    public function getEnvironments(): array
    {
        $relationship = $this->getRelationship('environments');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $environments = [];
        foreach ($relationship['data'] as $env) {
            $included = $this->findIncluded('environments', $env['id']);
            if ($included) {
                $environments[] = new EnvironmentResource($included, $this->included);
            }
        }

        return $environments;
    }
}
