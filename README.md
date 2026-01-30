# Laravel Cloud Connector

A Laravel package for interacting with the Laravel Cloud API.

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x

## Installation

### Via Composer (after publishing to Packagist)

```bash
composer require your-vendor/laravel-cloud-connector
```

### Local Development / Testing

Add the package to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../Laravel-MCP-Claude-Connector"
        }
    ],
    "require": {
        "your-vendor/laravel-cloud-connector": "*"
    }
}
```

Then run:

```bash
composer update
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-cloud-config
```

Add your API token to your `.env` file:

```env
LARAVEL_CLOUD_API_TOKEN=your-api-token-here
```

### Configuration Options

```php
// config/laravel-cloud.php
return [
    'api_token' => env('LARAVEL_CLOUD_API_TOKEN'),
    'base_url' => env('LARAVEL_CLOUD_API_URL', 'https://cloud.laravel.com/api'),
    'timeout' => env('LARAVEL_CLOUD_TIMEOUT', 30),
    'retry' => [
        'times' => env('LARAVEL_CLOUD_RETRY_TIMES', 3),
        'sleep' => env('LARAVEL_CLOUD_RETRY_SLEEP', 100),
    ],
];
```

## Usage

### Using the Facade

```php
use LaravelCloudConnector\Facades\LaravelCloud;

// List all applications
$applications = LaravelCloud::listApplications();

// Get a specific application
$app = LaravelCloud::getApplication('app-id');

// Create an application
$app = LaravelCloud::createApplication([
    'name' => 'My App',
    'repository' => 'owner/repo',
    'region' => 'us-east-1',
]);

// List environments
$environments = LaravelCloud::listEnvironments('app-id');

// Get environment details
$env = LaravelCloud::getEnvironment('env-id');

// Start/Stop environment
LaravelCloud::startEnvironment('env-id');
LaravelCloud::stopEnvironment('env-id');

// Add environment variables
LaravelCloud::addEnvironmentVariables('env-id', [
    ['key' => 'APP_DEBUG', 'value' => 'false'],
    ['key' => 'CACHE_DRIVER', 'value' => 'redis'],
]);

// Trigger a deployment
$deployment = LaravelCloud::createDeployment('env-id');

// Run a command
$command = LaravelCloud::runCommand('env-id', 'php artisan migrate');

// Check command status
$command = LaravelCloud::getCommand($command->getId());
if ($command->isSuccessful()) {
    echo $command->getOutput();
}
```

### Using Dependency Injection

```php
use LaravelCloudConnector\LaravelCloudClient;

class DeploymentController extends Controller
{
    public function __construct(
        protected LaravelCloudClient $cloud
    ) {}

    public function deploy(string $environmentId)
    {
        $deployment = $this->cloud->createDeployment($environmentId);

        return response()->json([
            'deployment_id' => $deployment->getId(),
            'status' => $deployment->getStatus(),
        ]);
    }
}
```

### Available Methods

#### Applications
- `listApplications(array $query = [])`
- `getApplication(string $id)`
- `createApplication(array $data)`
- `updateApplication(string $id, array $data)`

#### Environments
- `listEnvironments(string $applicationId)`
- `getEnvironment(string $id)`
- `createEnvironment(string $applicationId, array $data)`
- `updateEnvironment(string $id, array $data)`
- `deleteEnvironment(string $id)`
- `startEnvironment(string $id)`
- `stopEnvironment(string $id)`
- `addEnvironmentVariables(string $id, array $variables, string $method = 'set')`
- `replaceEnvironmentVariables(string $id, array $variables)`
- `getEnvironmentLogs(string $id, array $query = [])`

#### Deployments
- `listDeployments(string $environmentId)`
- `getDeployment(string $id)`
- `createDeployment(string $environmentId, array $data = [])`

#### Instances
- `listInstances(string $environmentId)`
- `getInstance(string $id)`
- `createInstance(string $environmentId, array $data)`
- `updateInstance(string $id, array $data)`
- `deleteInstance(string $id)`
- `listInstanceSizes(string $category = null)`

#### Commands
- `runCommand(string $environmentId, string $command)`
- `listCommands(string $environmentId)`
- `getCommand(string $id)`

#### Domains
- `listDomains(string $environmentId)`
- `createDomain(string $environmentId, array $data)`
- `getDomain(string $id)`
- `updateDomain(string $id, array $data)`
- `deleteDomain(string $id)`
- `verifyDomain(string $id)`

#### Database Clusters & Databases
- `listDatabaseClusters()`
- `createDatabaseCluster(array $data)`
- `getDatabaseCluster(string $id)`
- `updateDatabaseCluster(string $id, array $data)`
- `deleteDatabaseCluster(string $id)`
- `listDatabases(string $clusterId)`
- `createDatabase(string $clusterId, array $data)`
- `getDatabase(string $id)`
- `deleteDatabase(string $id)`

#### Caches
- `listCaches()`
- `createCache(array $data)`
- `getCache(string $id)`
- `updateCache(string $id, array $data)`
- `deleteCache(string $id)`
- `listCacheTypes()`

#### Object Storage
- `listBuckets()`
- `createBucket(array $data)`
- `getBucket(string $id)`
- `updateBucket(string $id, array $data)`
- `deleteBucket(string $id)`
- `listBucketKeys(string $bucketId)`
- `createBucketKey(string $bucketId, array $data)`
- `getBucketKey(string $id)`
- `deleteBucketKey(string $id)`

#### Meta
- `listRegions()`
- `listIpAddresses()`
- `getOrganization()`
- `listDatabaseTypes()`

### Exception Handling

```php
use LaravelCloudConnector\Exceptions\AuthenticationException;
use LaravelCloudConnector\Exceptions\NotFoundException;
use LaravelCloudConnector\Exceptions\ValidationException;
use LaravelCloudConnector\Exceptions\LaravelCloudException;

try {
    $app = LaravelCloud::createApplication($data);
} catch (AuthenticationException $e) {
    // Invalid or missing API token (401)
} catch (ValidationException $e) {
    // Validation errors (422)
    $errors = $e->getValidationErrors();
} catch (NotFoundException $e) {
    // Resource not found (404)
} catch (LaravelCloudException $e) {
    // Other API errors
    $response = $e->response;
}
```

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT License
