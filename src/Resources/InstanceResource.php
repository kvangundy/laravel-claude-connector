<?php

namespace LaravelCloudConnector\Resources;

class InstanceResource extends Resource
{
    public const SCALING_TYPE_NONE = 'none';
    public const SCALING_TYPE_CUSTOM = 'custom';
    public const SCALING_TYPE_AUTO = 'auto';

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getInstanceType(): ?string
    {
        return $this->getAttribute('type');
    }

    public function getSize(): ?string
    {
        return $this->getAttribute('size');
    }

    public function getScalingType(): ?string
    {
        return $this->getAttribute('scaling_type');
    }

    public function getMinReplicas(): ?int
    {
        return $this->getAttribute('min_replicas');
    }

    public function getMaxReplicas(): ?int
    {
        return $this->getAttribute('max_replicas');
    }

    public function getScalingCpuThreshold(): ?int
    {
        return $this->getAttribute('scaling_cpu_threshold_percentage');
    }

    public function getScalingMemoryThreshold(): ?int
    {
        return $this->getAttribute('scaling_memory_threshold_percentage');
    }

    public function usesScheduler(): bool
    {
        return (bool) $this->getAttribute('uses_scheduler', false);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function hasAutoScaling(): bool
    {
        return $this->getScalingType() === self::SCALING_TYPE_AUTO;
    }

    public function hasCustomScaling(): bool
    {
        return $this->getScalingType() === self::SCALING_TYPE_CUSTOM;
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

    public function getBackgroundProcesses(): array
    {
        $relationship = $this->getRelationship('background_processes');
        if (!$relationship || !isset($relationship['data'])) {
            return [];
        }

        $processes = [];
        foreach ($relationship['data'] as $process) {
            $included = $this->findIncluded('background_processes', $process['id']);
            if ($included) {
                $processes[] = new BackgroundProcessResource($included, $this->included);
            }
        }

        return $processes;
    }
}
