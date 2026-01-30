<?php

namespace LaravelCloudConnector;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LaravelCloudConnector\Exceptions\AuthenticationException;
use LaravelCloudConnector\Exceptions\LaravelCloudException;
use LaravelCloudConnector\Exceptions\NotFoundException;
use LaravelCloudConnector\Exceptions\ValidationException;
use LaravelCloudConnector\Resources\ApplicationResource;
use LaravelCloudConnector\Resources\BackgroundProcessResource;
use LaravelCloudConnector\Resources\BucketKeyResource;
use LaravelCloudConnector\Resources\BucketResource;
use LaravelCloudConnector\Resources\CacheResource;
use LaravelCloudConnector\Resources\CommandResource;
use LaravelCloudConnector\Resources\DatabaseClusterResource;
use LaravelCloudConnector\Resources\DatabaseResource;
use LaravelCloudConnector\Resources\DeploymentResource;
use LaravelCloudConnector\Resources\DomainResource;
use LaravelCloudConnector\Resources\EnvironmentResource;
use LaravelCloudConnector\Resources\InstanceResource;

class LaravelCloudClient
{
    protected PendingRequest $http;

    public function __construct(
        protected ?string $apiToken = null,
        protected string $baseUrl = 'https://cloud.laravel.com/api',
        protected int $timeout = 30,
        protected int $retryTimes = 3,
        protected int $retrySleep = 100,
    ) {
        $this->http = $this->createHttpClient();
    }

    protected function createHttpClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retrySleep, throw: false)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/json',
            ])
            ->when($this->apiToken, function (PendingRequest $request) {
                return $request->withToken($this->apiToken);
            });
    }

    public function setApiToken(string $token): self
    {
        $this->apiToken = $token;
        $this->http = $this->createHttpClient();

        return $this;
    }

    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        match ($response->status()) {
            401 => throw new AuthenticationException(),
            404 => throw new NotFoundException(),
            422 => throw ValidationException::fromResponse($response),
            default => throw LaravelCloudException::fromResponse($response),
        };
    }

    protected function parseResource(array $response, string $resourceClass): mixed
    {
        $data = $response['data'] ?? [];
        $included = $response['included'] ?? [];

        return new $resourceClass($data, $included);
    }

    protected function parseResourceCollection(array $response, string $resourceClass): array
    {
        $data = $response['data'] ?? [];
        $included = $response['included'] ?? [];

        return array_map(
            fn(array $item) => new $resourceClass($item, $included),
            $data
        );
    }

    // =========================================================================
    // Applications
    // =========================================================================

    /**
     * List all applications.
     *
     * @return ApplicationResource[]
     */
    public function listApplications(array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get('/applications', $query)
        );

        return $this->parseResourceCollection($response, ApplicationResource::class);
    }

    /**
     * Get a specific application.
     */
    public function getApplication(string $applicationId, array $query = []): ApplicationResource
    {
        $response = $this->handleResponse(
            $this->http->get("/applications/{$applicationId}", $query)
        );

        return $this->parseResource($response, ApplicationResource::class);
    }

    /**
     * Create a new application.
     */
    public function createApplication(array $data): ApplicationResource
    {
        $response = $this->handleResponse(
            $this->http->post('/applications', $data)
        );

        return $this->parseResource($response, ApplicationResource::class);
    }

    /**
     * Update an application.
     */
    public function updateApplication(string $applicationId, array $data): ApplicationResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/applications/{$applicationId}", $data)
        );

        return $this->parseResource($response, ApplicationResource::class);
    }

    // =========================================================================
    // Environments
    // =========================================================================

    /**
     * List environments for an application.
     *
     * @return EnvironmentResource[]
     */
    public function listEnvironments(string $applicationId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/applications/{$applicationId}/environments", $query)
        );

        return $this->parseResourceCollection($response, EnvironmentResource::class);
    }

    /**
     * Get a specific environment.
     */
    public function getEnvironment(string $environmentId, array $query = []): EnvironmentResource
    {
        $response = $this->handleResponse(
            $this->http->get("/environments/{$environmentId}", $query)
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Create a new environment.
     */
    public function createEnvironment(string $applicationId, array $data): EnvironmentResource
    {
        $response = $this->handleResponse(
            $this->http->post("/applications/{$applicationId}/environments", $data)
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Update an environment.
     */
    public function updateEnvironment(string $environmentId, array $data): EnvironmentResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/environments/{$environmentId}", $data)
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Delete an environment.
     */
    public function deleteEnvironment(string $environmentId): void
    {
        $this->handleResponse(
            $this->http->delete("/environments/{$environmentId}")
        );
    }

    /**
     * Start an environment.
     */
    public function startEnvironment(string $environmentId): EnvironmentResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/start")
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Stop an environment.
     */
    public function stopEnvironment(string $environmentId): EnvironmentResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/stop")
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Add environment variables.
     */
    public function addEnvironmentVariables(
        string $environmentId,
        array $variables,
        string $method = 'set'
    ): EnvironmentResource {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/variables", [
                'method' => $method,
                'variables' => $variables,
            ])
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Replace all environment variables.
     */
    public function replaceEnvironmentVariables(
        string $environmentId,
        array $variables
    ): EnvironmentResource {
        $response = $this->handleResponse(
            $this->http->put("/environments/{$environmentId}/variables", [
                'variables' => $variables,
            ])
        );

        return $this->parseResource($response, EnvironmentResource::class);
    }

    /**
     * Get environment logs.
     */
    public function getEnvironmentLogs(string $environmentId, array $query = []): array
    {
        return $this->handleResponse(
            $this->http->get("/environments/{$environmentId}/logs", $query)
        );
    }

    // =========================================================================
    // Deployments
    // =========================================================================

    /**
     * List deployments for an environment.
     *
     * @return DeploymentResource[]
     */
    public function listDeployments(string $environmentId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/environments/{$environmentId}/deployments", $query)
        );

        return $this->parseResourceCollection($response, DeploymentResource::class);
    }

    /**
     * Get a specific deployment.
     */
    public function getDeployment(string $deploymentId, array $query = []): DeploymentResource
    {
        $response = $this->handleResponse(
            $this->http->get("/deployments/{$deploymentId}", $query)
        );

        return $this->parseResource($response, DeploymentResource::class);
    }

    /**
     * Create/trigger a new deployment.
     */
    public function createDeployment(string $environmentId, array $data = []): DeploymentResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/deployments", $data)
        );

        return $this->parseResource($response, DeploymentResource::class);
    }

    // =========================================================================
    // Instances
    // =========================================================================

    /**
     * List instances for an environment.
     *
     * @return InstanceResource[]
     */
    public function listInstances(string $environmentId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/environments/{$environmentId}/instances", $query)
        );

        return $this->parseResourceCollection($response, InstanceResource::class);
    }

    /**
     * Get a specific instance.
     */
    public function getInstance(string $instanceId, array $query = []): InstanceResource
    {
        $response = $this->handleResponse(
            $this->http->get("/instances/{$instanceId}", $query)
        );

        return $this->parseResource($response, InstanceResource::class);
    }

    /**
     * Create a new instance.
     */
    public function createInstance(string $environmentId, array $data): InstanceResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/instances", $data)
        );

        return $this->parseResource($response, InstanceResource::class);
    }

    /**
     * Update an instance.
     */
    public function updateInstance(string $instanceId, array $data): InstanceResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/instances/{$instanceId}", $data)
        );

        return $this->parseResource($response, InstanceResource::class);
    }

    /**
     * Delete an instance.
     */
    public function deleteInstance(string $instanceId): void
    {
        $this->handleResponse(
            $this->http->delete("/instances/{$instanceId}")
        );
    }

    /**
     * List available instance sizes.
     */
    public function listInstanceSizes(?string $category = null): array
    {
        $query = $category ? ['category' => $category] : [];

        return $this->handleResponse(
            $this->http->get('/instance-sizes', $query)
        );
    }

    // =========================================================================
    // Commands
    // =========================================================================

    /**
     * Run a command on an environment.
     */
    public function runCommand(string $environmentId, string $command): CommandResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/commands", [
                'command' => $command,
            ])
        );

        return $this->parseResource($response, CommandResource::class);
    }

    /**
     * List commands for an environment.
     *
     * @return CommandResource[]
     */
    public function listCommands(string $environmentId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/environments/{$environmentId}/commands", $query)
        );

        return $this->parseResourceCollection($response, CommandResource::class);
    }

    /**
     * Get a specific command.
     */
    public function getCommand(string $commandId, array $query = []): CommandResource
    {
        $response = $this->handleResponse(
            $this->http->get("/commands/{$commandId}", $query)
        );

        return $this->parseResource($response, CommandResource::class);
    }

    // =========================================================================
    // Domains
    // =========================================================================

    /**
     * List domains for an environment.
     *
     * @return DomainResource[]
     */
    public function listDomains(string $environmentId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/environments/{$environmentId}/domains", $query)
        );

        return $this->parseResourceCollection($response, DomainResource::class);
    }

    /**
     * Create a new domain.
     */
    public function createDomain(string $environmentId, array $data): DomainResource
    {
        $response = $this->handleResponse(
            $this->http->post("/environments/{$environmentId}/domains", $data)
        );

        return $this->parseResource($response, DomainResource::class);
    }

    /**
     * Get a specific domain.
     */
    public function getDomain(string $domainId, array $query = []): DomainResource
    {
        $response = $this->handleResponse(
            $this->http->get("/domains/{$domainId}", $query)
        );

        return $this->parseResource($response, DomainResource::class);
    }

    /**
     * Update a domain.
     */
    public function updateDomain(string $domainId, array $data): DomainResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/domains/{$domainId}", $data)
        );

        return $this->parseResource($response, DomainResource::class);
    }

    /**
     * Delete a domain.
     */
    public function deleteDomain(string $domainId): void
    {
        $this->handleResponse(
            $this->http->delete("/domains/{$domainId}")
        );
    }

    /**
     * Verify a domain.
     */
    public function verifyDomain(string $domainId): DomainResource
    {
        $response = $this->handleResponse(
            $this->http->post("/domains/{$domainId}/verify")
        );

        return $this->parseResource($response, DomainResource::class);
    }

    // =========================================================================
    // Background Processes
    // =========================================================================

    /**
     * List background processes for an instance.
     *
     * @return BackgroundProcessResource[]
     */
    public function listBackgroundProcesses(string $instanceId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/instances/{$instanceId}/background-processes", $query)
        );

        return $this->parseResourceCollection($response, BackgroundProcessResource::class);
    }

    /**
     * Create a background process.
     */
    public function createBackgroundProcess(string $instanceId, array $data): BackgroundProcessResource
    {
        $response = $this->handleResponse(
            $this->http->post("/instances/{$instanceId}/background-processes", $data)
        );

        return $this->parseResource($response, BackgroundProcessResource::class);
    }

    /**
     * Get a specific background process.
     */
    public function getBackgroundProcess(string $processId, array $query = []): BackgroundProcessResource
    {
        $response = $this->handleResponse(
            $this->http->get("/background-processes/{$processId}", $query)
        );

        return $this->parseResource($response, BackgroundProcessResource::class);
    }

    /**
     * Update a background process.
     */
    public function updateBackgroundProcess(string $processId, array $data): BackgroundProcessResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/background-processes/{$processId}", $data)
        );

        return $this->parseResource($response, BackgroundProcessResource::class);
    }

    /**
     * Delete a background process.
     */
    public function deleteBackgroundProcess(string $processId): void
    {
        $this->handleResponse(
            $this->http->delete("/background-processes/{$processId}")
        );
    }

    // =========================================================================
    // Database Clusters
    // =========================================================================

    /**
     * List database clusters.
     *
     * @return DatabaseClusterResource[]
     */
    public function listDatabaseClusters(array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get('/database-clusters', $query)
        );

        return $this->parseResourceCollection($response, DatabaseClusterResource::class);
    }

    /**
     * Create a database cluster.
     */
    public function createDatabaseCluster(array $data): DatabaseClusterResource
    {
        $response = $this->handleResponse(
            $this->http->post('/database-clusters', $data)
        );

        return $this->parseResource($response, DatabaseClusterResource::class);
    }

    /**
     * Get a specific database cluster.
     */
    public function getDatabaseCluster(string $clusterId, array $query = []): DatabaseClusterResource
    {
        $response = $this->handleResponse(
            $this->http->get("/database-clusters/{$clusterId}", $query)
        );

        return $this->parseResource($response, DatabaseClusterResource::class);
    }

    /**
     * Update a database cluster.
     */
    public function updateDatabaseCluster(string $clusterId, array $data): DatabaseClusterResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/database-clusters/{$clusterId}", $data)
        );

        return $this->parseResource($response, DatabaseClusterResource::class);
    }

    /**
     * Delete a database cluster.
     */
    public function deleteDatabaseCluster(string $clusterId): void
    {
        $this->handleResponse(
            $this->http->delete("/database-clusters/{$clusterId}")
        );
    }

    // =========================================================================
    // Databases
    // =========================================================================

    /**
     * List databases in a cluster.
     *
     * @return DatabaseResource[]
     */
    public function listDatabases(string $clusterId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/database-clusters/{$clusterId}/databases", $query)
        );

        return $this->parseResourceCollection($response, DatabaseResource::class);
    }

    /**
     * Create a database.
     */
    public function createDatabase(string $clusterId, array $data): DatabaseResource
    {
        $response = $this->handleResponse(
            $this->http->post("/database-clusters/{$clusterId}/databases", $data)
        );

        return $this->parseResource($response, DatabaseResource::class);
    }

    /**
     * Get a specific database.
     */
    public function getDatabase(string $databaseId, array $query = []): DatabaseResource
    {
        $response = $this->handleResponse(
            $this->http->get("/databases/{$databaseId}", $query)
        );

        return $this->parseResource($response, DatabaseResource::class);
    }

    /**
     * Delete a database.
     */
    public function deleteDatabase(string $databaseId): void
    {
        $this->handleResponse(
            $this->http->delete("/databases/{$databaseId}")
        );
    }

    // =========================================================================
    // Caches
    // =========================================================================

    /**
     * List caches.
     *
     * @return CacheResource[]
     */
    public function listCaches(array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get('/caches', $query)
        );

        return $this->parseResourceCollection($response, CacheResource::class);
    }

    /**
     * Create a cache.
     */
    public function createCache(array $data): CacheResource
    {
        $response = $this->handleResponse(
            $this->http->post('/caches', $data)
        );

        return $this->parseResource($response, CacheResource::class);
    }

    /**
     * Get a specific cache.
     */
    public function getCache(string $cacheId, array $query = []): CacheResource
    {
        $response = $this->handleResponse(
            $this->http->get("/caches/{$cacheId}", $query)
        );

        return $this->parseResource($response, CacheResource::class);
    }

    /**
     * Update a cache.
     */
    public function updateCache(string $cacheId, array $data): CacheResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/caches/{$cacheId}", $data)
        );

        return $this->parseResource($response, CacheResource::class);
    }

    /**
     * Delete a cache.
     */
    public function deleteCache(string $cacheId): void
    {
        $this->handleResponse(
            $this->http->delete("/caches/{$cacheId}")
        );
    }

    /**
     * List available cache types.
     */
    public function listCacheTypes(): array
    {
        return $this->handleResponse(
            $this->http->get('/cache-types')
        );
    }

    // =========================================================================
    // Object Storage (Buckets)
    // =========================================================================

    /**
     * List buckets.
     *
     * @return BucketResource[]
     */
    public function listBuckets(array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get('/buckets', $query)
        );

        return $this->parseResourceCollection($response, BucketResource::class);
    }

    /**
     * Create a bucket.
     */
    public function createBucket(array $data): BucketResource
    {
        $response = $this->handleResponse(
            $this->http->post('/buckets', $data)
        );

        return $this->parseResource($response, BucketResource::class);
    }

    /**
     * Get a specific bucket.
     */
    public function getBucket(string $bucketId, array $query = []): BucketResource
    {
        $response = $this->handleResponse(
            $this->http->get("/buckets/{$bucketId}", $query)
        );

        return $this->parseResource($response, BucketResource::class);
    }

    /**
     * Update a bucket.
     */
    public function updateBucket(string $bucketId, array $data): BucketResource
    {
        $response = $this->handleResponse(
            $this->http->patch("/buckets/{$bucketId}", $data)
        );

        return $this->parseResource($response, BucketResource::class);
    }

    /**
     * Delete a bucket.
     */
    public function deleteBucket(string $bucketId): void
    {
        $this->handleResponse(
            $this->http->delete("/buckets/{$bucketId}")
        );
    }

    // =========================================================================
    // Bucket Keys
    // =========================================================================

    /**
     * List keys for a bucket.
     *
     * @return BucketKeyResource[]
     */
    public function listBucketKeys(string $bucketId, array $query = []): array
    {
        $response = $this->handleResponse(
            $this->http->get("/buckets/{$bucketId}/keys", $query)
        );

        return $this->parseResourceCollection($response, BucketKeyResource::class);
    }

    /**
     * Create a bucket key.
     */
    public function createBucketKey(string $bucketId, array $data): BucketKeyResource
    {
        $response = $this->handleResponse(
            $this->http->post("/buckets/{$bucketId}/keys", $data)
        );

        return $this->parseResource($response, BucketKeyResource::class);
    }

    /**
     * Get a specific bucket key.
     */
    public function getBucketKey(string $keyId, array $query = []): BucketKeyResource
    {
        $response = $this->handleResponse(
            $this->http->get("/bucket-keys/{$keyId}", $query)
        );

        return $this->parseResource($response, BucketKeyResource::class);
    }

    /**
     * Delete a bucket key.
     */
    public function deleteBucketKey(string $keyId): void
    {
        $this->handleResponse(
            $this->http->delete("/bucket-keys/{$keyId}")
        );
    }

    // =========================================================================
    // Meta / Utilities
    // =========================================================================

    /**
     * List available regions.
     */
    public function listRegions(): array
    {
        return $this->handleResponse(
            $this->http->get('/regions')
        );
    }

    /**
     * List IP addresses to whitelist.
     */
    public function listIpAddresses(): array
    {
        return $this->handleResponse(
            $this->http->get('/ips')
        );
    }

    /**
     * Get organization details.
     */
    public function getOrganization(array $query = []): array
    {
        return $this->handleResponse(
            $this->http->get('/organization', $query)
        );
    }

    /**
     * List available database types.
     */
    public function listDatabaseTypes(): array
    {
        return $this->handleResponse(
            $this->http->get('/database-types')
        );
    }
}
