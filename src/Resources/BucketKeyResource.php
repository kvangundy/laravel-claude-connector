<?php

namespace LaravelCloudConnector\Resources;

class BucketKeyResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getAccessKeyId(): ?string
    {
        return $this->getAttribute('access_key_id');
    }

    public function getSecretAccessKey(): ?string
    {
        return $this->getAttribute('secret_access_key');
    }

    public function getPermissions(): ?array
    {
        return $this->getAttribute('permissions');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getBucket(): ?BucketResource
    {
        $relationship = $this->getRelationship('bucket');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        $included = $this->findIncluded('buckets', $relationship['data']['id']);
        return $included ? new BucketResource($included, $this->included) : null;
    }
}
