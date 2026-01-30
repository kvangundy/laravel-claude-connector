<?php

namespace LaravelCloudConnector\Resources;

class BucketResource extends Resource
{
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getRegion(): ?string
    {
        return $this->getAttribute('region');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getKeys(): array
    {
        $relationship = $this->getRelationship('keys');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $keys = [];
        foreach ($relationship['data'] as $key) {
            $included = $this->findIncluded('bucket_keys', $key['id']);
            if ($included) {
                $keys[] = new BucketKeyResource($included, $this->included);
            }
        }

        return $keys;
    }
}
