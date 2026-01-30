<?php

namespace LaravelCloudConnector\Resources;

class DeploymentResource extends Resource
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_BUILD_PENDING = 'build.pending';
    public const STATUS_BUILD_RUNNING = 'build.running';
    public const STATUS_BUILD_SUCCEEDED = 'build.succeeded';
    public const STATUS_BUILD_FAILED = 'build.failed';
    public const STATUS_DEPLOYMENT_PENDING = 'deployment.pending';
    public const STATUS_DEPLOYMENT_RUNNING = 'deployment.running';
    public const STATUS_DEPLOYMENT_SUCCEEDED = 'deployment.succeeded';
    public const STATUS_DEPLOYMENT_FAILED = 'deployment.failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getBranchName(): ?string
    {
        return $this->getAttribute('branch_name');
    }

    public function getCommitHash(): ?string
    {
        return $this->getAttribute('commit_hash');
    }

    public function getCommitMessage(): ?string
    {
        return $this->getAttribute('commit_message');
    }

    public function getPhpVersion(): ?string
    {
        return $this->getAttribute('php_version');
    }

    public function getNodeVersion(): ?string
    {
        return $this->getAttribute('node_version');
    }

    public function getBuildCommand(): ?string
    {
        return $this->getAttribute('build_command');
    }

    public function usesOctane(): bool
    {
        return (bool) $this->getAttribute('uses_octane', false);
    }

    public function supportsHibernation(): bool
    {
        return (bool) $this->getAttribute('supports_hibernation', false);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getStartedAt(): ?string
    {
        return $this->getAttribute('started_at');
    }

    public function getFinishedAt(): ?string
    {
        return $this->getAttribute('finished_at');
    }

    public function isSuccessful(): bool
    {
        return $this->getStatus() === self::STATUS_DEPLOYMENT_SUCCEEDED;
    }

    public function isFailed(): bool
    {
        return in_array($this->getStatus(), [
            self::STATUS_BUILD_FAILED,
            self::STATUS_DEPLOYMENT_FAILED,
            self::STATUS_FAILED,
        ]);
    }

    public function isPending(): bool
    {
        return in_array($this->getStatus(), [
            self::STATUS_PENDING,
            self::STATUS_BUILD_PENDING,
            self::STATUS_DEPLOYMENT_PENDING,
        ]);
    }

    public function isRunning(): bool
    {
        return in_array($this->getStatus(), [
            self::STATUS_BUILD_RUNNING,
            self::STATUS_DEPLOYMENT_RUNNING,
        ]);
    }

    public function isCancelled(): bool
    {
        return $this->getStatus() === self::STATUS_CANCELLED;
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

    public function getInitiator(): ?array
    {
        $relationship = $this->getRelationship('initiator');
        if (!$relationship || !isset($relationship['data'])) {
            return null;
        }

        return $this->findIncluded('users', $relationship['data']['id']);
    }
}
