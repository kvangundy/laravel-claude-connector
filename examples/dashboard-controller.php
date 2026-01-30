<?php

/**
 * Example: A Laravel controller that uses the connector to build a dashboard.
 *
 * This would be part of a separate Laravel application that you deploy to Laravel Cloud.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelCloudConnector\Facades\LaravelCloud;
use LaravelCloudConnector\Exceptions\LaravelCloudException;

class CloudDashboardController extends Controller
{
    /**
     * Display all applications and their environments.
     */
    public function index()
    {
        $applications = LaravelCloud::listApplications();

        return view('dashboard.index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show details for a specific environment.
     */
    public function environment(string $environmentId)
    {
        $environment = LaravelCloud::getEnvironment($environmentId, [
            'include' => 'application,instances,domains',
        ]);

        $deployments = LaravelCloud::listDeployments($environmentId, [
            'page[size]' => 10,
        ]);

        return view('dashboard.environment', [
            'environment' => $environment,
            'deployments' => $deployments,
        ]);
    }

    /**
     * Trigger a new deployment.
     */
    public function deploy(Request $request, string $environmentId)
    {
        try {
            $deployment = LaravelCloud::createDeployment($environmentId);

            return redirect()
                ->back()
                ->with('success', "Deployment {$deployment->getId()} started!");
        } catch (LaravelCloudException $e) {
            return redirect()
                ->back()
                ->with('error', "Deployment failed: {$e->getMessage()}");
        }
    }

    /**
     * Run an artisan command.
     */
    public function runCommand(Request $request, string $environmentId)
    {
        $request->validate([
            'command' => 'required|string|max:1000',
        ]);

        $command = LaravelCloud::runCommand(
            $environmentId,
            $request->input('command')
        );

        return response()->json([
            'command_id' => $command->getId(),
            'status' => $command->getStatus(),
        ]);
    }

    /**
     * Poll command status (for AJAX).
     */
    public function commandStatus(string $commandId)
    {
        $command = LaravelCloud::getCommand($commandId);

        return response()->json([
            'status' => $command->getStatus(),
            'output' => $command->getOutput(),
            'exit_code' => $command->getExitCode(),
            'is_complete' => $command->isComplete(),
        ]);
    }
}
