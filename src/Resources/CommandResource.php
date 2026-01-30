<?php

namespace LaravelCloudConnector\Resources;

class CommandResource extends Resource
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CREATED = 'command.created';
    public const STATUS_RUNNING = 'command.running';
    public const STATUS_SUCCESS = 'command.success';
    public const STATUS_FAILURE = 'command.failure';

    public function getCommand(): ?string
    {
        return $this->getAttribute('command');
    }

    public function getOutput(): ?string
    {
        return $this->getAttribute('output');
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getExitCode(): ?int
    {
        return $this->getAttribute('exit_code');
    }

    public function getFailureReason(): ?string
    {
        return $this->getAttribute('failure_reason');
    }

    public function getStartedAt(): ?string
    {
        return $this->getAttribute('started_at');
    }

    public function getFinishedAt(): ?string
    {
        return $this->getAttribute('finished_at');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function isSuccessful(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === self::STATUS_FAILURE;
    }

    public function isRunning(): bool
    {
        return $this->getStatus() === self::STATUS_RUNNING;
    }

    public function isPending(): bool
    {
        return in_array($this->getStatus(), [
            self::STATUS_PENDING,
            self::STATUS_CREATED,
        ]);
    }

    public function isComplete(): bool
    {
        return in_array($this->getStatus(), [
            self::STATUS_SUCCESS,
            self::STATUS_FAILURE,
        ]);
    }
}
