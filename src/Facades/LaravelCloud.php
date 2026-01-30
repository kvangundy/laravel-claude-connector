<?php

namespace LaravelCloudConnector\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelCloudConnector\LaravelCloudClient;

/**
 * @method static \LaravelCloudConnector\Resources\ApplicationResource[] listApplications(array $query = [])
 * @method static \LaravelCloudConnector\Resources\ApplicationResource getApplication(string $applicationId)
 * @method static \LaravelCloudConnector\Resources\ApplicationResource createApplication(array $data)
 * @method static \LaravelCloudConnector\Resources\ApplicationResource updateApplication(string $applicationId, array $data)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource[] listEnvironments(string $applicationId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource getEnvironment(string $environmentId)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource createEnvironment(string $applicationId, array $data)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource updateEnvironment(string $environmentId, array $data)
 * @method static void deleteEnvironment(string $environmentId)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource startEnvironment(string $environmentId)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource stopEnvironment(string $environmentId)
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource addEnvironmentVariables(string $environmentId, array $variables, string $method = 'set')
 * @method static \LaravelCloudConnector\Resources\EnvironmentResource replaceEnvironmentVariables(string $environmentId, array $variables)
 * @method static \LaravelCloudConnector\Resources\DeploymentResource[] listDeployments(string $environmentId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\DeploymentResource getDeployment(string $deploymentId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\DeploymentResource createDeployment(string $environmentId, array $data = [])
 * @method static \LaravelCloudConnector\Resources\InstanceResource[] listInstances(string $environmentId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\InstanceResource getInstance(string $instanceId)
 * @method static \LaravelCloudConnector\Resources\InstanceResource createInstance(string $environmentId, array $data)
 * @method static \LaravelCloudConnector\Resources\InstanceResource updateInstance(string $instanceId, array $data)
 * @method static void deleteInstance(string $instanceId)
 * @method static \LaravelCloudConnector\Resources\CommandResource runCommand(string $environmentId, string $command)
 * @method static \LaravelCloudConnector\Resources\CommandResource[] listCommands(string $environmentId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\CommandResource getCommand(string $commandId)
 * @method static \LaravelCloudConnector\Resources\DomainResource[] listDomains(string $environmentId, array $query = [])
 * @method static \LaravelCloudConnector\Resources\DomainResource createDomain(string $environmentId, array $data)
 * @method static \LaravelCloudConnector\Resources\DomainResource getDomain(string $domainId)
 * @method static \LaravelCloudConnector\Resources\DomainResource updateDomain(string $domainId, array $data)
 * @method static void deleteDomain(string $domainId)
 * @method static \LaravelCloudConnector\Resources\DomainResource verifyDomain(string $domainId)
 * @method static array listRegions()
 * @method static array listIpAddresses()
 * @method static array listInstanceSizes(string $category = null)
 *
 * @see \LaravelCloudConnector\LaravelCloudClient
 */
class LaravelCloud extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelCloudClient::class;
    }
}
