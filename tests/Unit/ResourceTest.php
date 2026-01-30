<?php

namespace LaravelCloudConnector\Tests\Unit;

use LaravelCloudConnector\Resources\ApplicationResource;
use LaravelCloudConnector\Resources\DeploymentResource;
use LaravelCloudConnector\Resources\EnvironmentResource;
use LaravelCloudConnector\Tests\TestCase;

class ResourceTest extends TestCase
{
    public function test_application_resource_parses_data(): void
    {
        $data = [
            'id' => 'app-123',
            'type' => 'applications',
            'attributes' => [
                'name' => 'My Application',
                'slug' => 'my-application',
                'region' => 'us-east-1',
                'avatar_url' => 'https://example.com/avatar.png',
                'created_at' => '2025-01-01T00:00:00Z',
            ],
            'relationships' => [
                'environments' => [
                    'data' => [
                        ['type' => 'environments', 'id' => 'env-1'],
                    ],
                ],
            ],
        ];

        $resource = new ApplicationResource($data);

        $this->assertEquals('app-123', $resource->getId());
        $this->assertEquals('applications', $resource->getType());
        $this->assertEquals('My Application', $resource->getName());
        $this->assertEquals('my-application', $resource->getSlug());
        $this->assertEquals('us-east-1', $resource->getRegion());
    }

    public function test_environment_resource_parses_data(): void
    {
        $data = [
            'id' => 'env-456',
            'type' => 'environments',
            'attributes' => [
                'name' => 'production',
                'status' => 'running',
                'branch' => 'main',
                'php_version' => '8.3',
            ],
        ];

        $resource = new EnvironmentResource($data);

        $this->assertEquals('env-456', $resource->getId());
        $this->assertEquals('production', $resource->getName());
        $this->assertEquals('running', $resource->getStatus());
        $this->assertTrue($resource->isRunning());
        $this->assertFalse($resource->isStopped());
    }

    public function test_deployment_resource_status_helpers(): void
    {
        $successfulDeployment = new DeploymentResource([
            'id' => 'dep-1',
            'type' => 'deployments',
            'attributes' => [
                'status' => DeploymentResource::STATUS_DEPLOYMENT_SUCCEEDED,
            ],
        ]);

        $this->assertTrue($successfulDeployment->isSuccessful());
        $this->assertFalse($successfulDeployment->isFailed());

        $failedDeployment = new DeploymentResource([
            'id' => 'dep-2',
            'type' => 'deployments',
            'attributes' => [
                'status' => DeploymentResource::STATUS_BUILD_FAILED,
            ],
        ]);

        $this->assertTrue($failedDeployment->isFailed());
        $this->assertFalse($failedDeployment->isSuccessful());
    }

    public function test_resource_array_access(): void
    {
        $data = [
            'id' => 'app-123',
            'type' => 'applications',
            'attributes' => [
                'name' => 'Test App',
                'region' => 'us-east-1',
            ],
        ];

        $resource = new ApplicationResource($data);

        $this->assertEquals('Test App', $resource['name']);
        $this->assertEquals('us-east-1', $resource['region']);
        $this->assertTrue(isset($resource['name']));
        $this->assertFalse(isset($resource['nonexistent']));
    }

    public function test_resource_json_serialization(): void
    {
        $data = [
            'id' => 'app-123',
            'type' => 'applications',
            'attributes' => [
                'name' => 'Test App',
            ],
        ];

        $resource = new ApplicationResource($data);
        $json = json_encode($resource);
        $decoded = json_decode($json, true);

        $this->assertEquals('app-123', $decoded['id']);
        $this->assertEquals('applications', $decoded['type']);
        $this->assertEquals('Test App', $decoded['attributes']['name']);
    }
}
