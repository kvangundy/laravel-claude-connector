<?php

namespace LaravelCloudConnector\Resources;

class DatabaseResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getUsername(): ?string
    {
        return $this->getAttribute('username');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getCluster(): ?DatabaseClusterResource
    {
        $relationship = $this->getRelationship('cluster');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        $included = $this->findIncluded('database_clusters', $relationship['data']['id']);
        return $included ? new DatabaseClusterResource($included, $this->included) : null;
    }
}
