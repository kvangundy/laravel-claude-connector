<?php

namespace LaravelCloudConnector\Resources;

use ArrayAccess;
use JsonSerializable;

abstract class Resource implements ArrayAccess, JsonSerializable
{
    protected array $attributes = [];
    protected array $relationships = [];
    protected array $included = [];

    public function __construct(array $data, array $included = [])
    {
        $this->attributes = $data['attributes'] ?? [];
        $this->attributes['id'] = $data['id'] ?? null;
        $this->attributes['type'] = $data['type'] ?? null;
        $this->relationships = $data['relationships'] ?? [];
        $this->included = $included;
    }

    public function getId(): ?string
    {
        return $this->attributes['id'];
    }

    public function getType(): ?string
    {
        return $this->attributes['type'];
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function getRelationship(string $name): ?array
    {
        return $this->relationships[$name] ?? null;
    }

    public function getIncluded(): array
    {
        return $this->included;
    }

    public function findIncluded(string $type, string $id): ?array
    {
        foreach ($this->included as $item) {
            if (($item['type'] ?? null) === $type && ($item['id'] ?? null) === $id) {
                return $item;
            }
        }
        return null;
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'attributes' => $this->attributes,
            'relationships' => $this->relationships,
        ];
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
