<?php

namespace LaravelCloudConnector\Tests\Feature;

use Illuminate\Support\Facades\Http;
use LaravelCloudConnector\Exceptions\AuthenticationException;
use LaravelCloudConnector\Exceptions\NotFoundException;
use LaravelCloudConnector\Exceptions\ValidationException;
use LaravelCloudConnector\Facades\LaravelCloud;
use LaravelCloudConnector\LaravelCloudClient;
use LaravelCloudConnector\Resources\ApplicationResource;
use LaravelCloudConnector\Resources\CommandResource;
use LaravelCloudConnector\Resources\DeploymentResource;
use LaravelCloudConnector\Resources\EnvironmentResource;
use LaravelCloudConnector\Tests\TestCase;

class LaravelCloudClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_list_applications(): void
    {
        Http::fake([
            'cloud.laravel.com/api/applications*' => Http::response([
                'data' => [
                    [
                        'id' => 'app-1',
                        'type' => 'applications',
                        'attributes' => [
                            'name' => 'App One',
                            'slug' => 'app-one',
                            'region' => 'us-east-1',
                        ],
                    ],
                    [
                        'id' => 'app-2',
                        'type' => 'applications',
                        'attributes' => [
                            'name' => 'App Two',
                            'slug' => 'app-two',
                            'region' => 'eu-west-1',
                        ],
                    ],
                ],
            ]),
        ]);

        $applications = LaravelCloud::listApplications();

        $this->assertCount(2, $applications);
        $this->assertInstanceOf(ApplicationResource::class, $applications[0]);
        $this->assertEquals('App One', $applications[0]->getName());
        $this->assertEquals('App Two', $applications[1]->getName());
    }

    public function test_get_application(): void
    {
        Http::fake([
            'cloud.laravel.com/api/applications/app-123*' => Http::response([
                'data' => [
                    'id' => 'app-123',
                    'type' => 'applications',
                    'attributes' => [
                        'name' => 'My App',
                        'slug' => 'my-app',
                        'region' => 'us-east-2',
                    ],
                ],
            ]),
        ]);

        $application = LaravelCloud::getApplication('app-123');

        $this->assertInstanceOf(ApplicationResource::class, $application);
        $this->assertEquals('app-123', $application->getId());
        $this->assertEquals('My App', $application->getName());
    }

    public function test_create_application(): void
    {
        Http::fake([
            'cloud.laravel.com/api/applications' => Http::response([
                'data' => [
                    'id' => 'app-new',
                    'type' => 'applications',
                    'attributes' => [
                        'name' => 'New Application',
                        'slug' => 'new-application',
                        'region' => 'us-east-1',
                    ],
                ],
            ], 201),
        ]);

        $application = LaravelCloud::createApplication([
            'name' => 'New Application',
            'repository' => 'owner/repo',
            'region' => 'us-east-1',
        ]);

        $this->assertInstanceOf(ApplicationResource::class, $application);
        $this->assertEquals('New Application', $application->getName());

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['name'] === 'New Application'
                && $request['repository'] === 'owner/repo';
        });
    }

    public function test_list_deployments(): void
    {
        Http::fake([
            'cloud.laravel.com/api/environments/*/deployments*' => Http::response([
                'data' => [
                    [
                        'id' => 'dep-1',
                        'type' => 'deployments',
                        'attributes' => [
                            'status' => 'deployment.succeeded',
                            'branch_name' => 'main',
                            'commit_hash' => 'abc123',
                        ],
                    ],
                ],
                'links' => [
                    'first' => 'https://cloud.laravel.com/api/environments/env-1/deployments?page=1',
                    'last' => 'https://cloud.laravel.com/api/environments/env-1/deployments?page=1',
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 1,
                ],
            ]),
        ]);

        $deployments = LaravelCloud::listDeployments('env-123');

        $this->assertCount(1, $deployments);
        $this->assertInstanceOf(DeploymentResource::class, $deployments[0]);
        $this->assertTrue($deployments[0]->isSuccessful());
    }

    public function test_run_command(): void
    {
        Http::fake([
            'cloud.laravel.com/api/environments/*/commands' => Http::response([
                'data' => [
                    'id' => 'cmd-1',
                    'type' => 'commands',
                    'attributes' => [
                        'command' => 'php artisan migrate',
                        'status' => 'pending',
                        'output' => null,
                    ],
                ],
            ]),
        ]);

        $command = LaravelCloud::runCommand('env-123', 'php artisan migrate');

        $this->assertInstanceOf(CommandResource::class, $command);
        $this->assertEquals('php artisan migrate', $command->getCommand());
        $this->assertTrue($command->isPending());
    }

    public function test_add_environment_variables(): void
    {
        Http::fake([
            'cloud.laravel.com/api/environments/*/variables' => Http::response([
                'data' => [
                    'id' => 'env-123',
                    'type' => 'environments',
                    'attributes' => [
                        'name' => 'production',
                        'status' => 'running',
                    ],
                ],
            ]),
        ]);

        $environment = LaravelCloud::addEnvironmentVariables('env-123', [
            ['key' => 'APP_DEBUG', 'value' => 'false'],
            ['key' => 'CACHE_DRIVER', 'value' => 'redis'],
        ]);

        $this->assertInstanceOf(EnvironmentResource::class, $environment);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['method'] === 'set'
                && count($request['variables']) === 2;
        });
    }

    public function test_authentication_exception_on_401(): void
    {
        Http::fake([
            'cloud.laravel.com/api/*' => Http::response(['message' => 'Unauthenticated'], 401),
        ]);

        $this->expectException(AuthenticationException::class);

        LaravelCloud::listApplications();
    }

    public function test_not_found_exception_on_404(): void
    {
        Http::fake([
            'cloud.laravel.com/api/*' => Http::response(['message' => 'Not found'], 404),
        ]);

        $this->expectException(NotFoundException::class);

        LaravelCloud::getApplication('nonexistent');
    }

    public function test_validation_exception_on_422(): void
    {
        Http::fake([
            'cloud.laravel.com/api/*' => Http::response([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ], 422),
        ]);

        $this->expectException(ValidationException::class);

        LaravelCloud::createApplication([]);
    }

    public function test_client_can_change_api_token(): void
    {
        $client = new LaravelCloudClient('initial-token');

        Http::fake([
            'cloud.laravel.com/api/*' => Http::response(['data' => []]),
        ]);

        $client->setApiToken('new-token');
        $client->listApplications();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer new-token');
        });
    }

    public function test_facade_resolves_to_client(): void
    {
        $this->assertInstanceOf(
            LaravelCloudClient::class,
            app(LaravelCloudClient::class)
        );
    }
}
