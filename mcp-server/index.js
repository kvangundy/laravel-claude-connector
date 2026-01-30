#!/usr/bin/env node

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";

const API_TOKEN = process.env.LARAVEL_CLOUD_API_TOKEN;
const BASE_URL = process.env.LARAVEL_CLOUD_API_URL || "https://cloud.laravel.com/api";

if (!API_TOKEN) {
  console.error("Error: LARAVEL_CLOUD_API_TOKEN environment variable is required");
  process.exit(1);
}

async function apiRequest(method, path, body = null) {
  const url = `${BASE_URL}${path}`;
  const options = {
    method,
    headers: {
      "Authorization": `Bearer ${API_TOKEN}`,
      "Accept": "application/vnd.api+json",
      "Content-Type": "application/json",
    },
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(url, options);
  const data = await response.json();

  if (!response.ok) {
    throw new Error(`API Error ${response.status}: ${data.message || JSON.stringify(data)}`);
  }

  return data;
}

function formatResource(resource) {
  if (!resource) return null;
  return {
    id: resource.id,
    type: resource.type,
    ...resource.attributes,
  };
}

function formatResourceList(response) {
  const data = response.data || [];
  return data.map(formatResource);
}

// Define all available tools
const tools = [
  // Applications
  {
    name: "list_applications",
    description: "List all Laravel Cloud applications in your organization",
    inputSchema: {
      type: "object",
      properties: {},
    },
  },
  {
    name: "get_application",
    description: "Get details of a specific Laravel Cloud application",
    inputSchema: {
      type: "object",
      properties: {
        application_id: {
          type: "string",
          description: "The application ID",
        },
      },
      required: ["application_id"],
    },
  },
  {
    name: "create_application",
    description: "Create a new Laravel Cloud application",
    inputSchema: {
      type: "object",
      properties: {
        name: {
          type: "string",
          description: "Application name (3-40 characters)",
        },
        repository: {
          type: "string",
          description: "GitHub repository (e.g., 'owner/repo')",
        },
        region: {
          type: "string",
          description: "Cloud region (e.g., 'us-east-1', 'eu-west-1')",
          enum: ["us-east-2", "us-east-1", "eu-central-1", "eu-west-1", "eu-west-2", "ap-southeast-1", "ap-southeast-2", "ca-central-1", "me-central-1"],
        },
      },
      required: ["name", "repository", "region"],
    },
  },

  // Environments
  {
    name: "list_environments",
    description: "List all environments for an application",
    inputSchema: {
      type: "object",
      properties: {
        application_id: {
          type: "string",
          description: "The application ID",
        },
      },
      required: ["application_id"],
    },
  },
  {
    name: "get_environment",
    description: "Get details of a specific environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },
  {
    name: "start_environment",
    description: "Start a stopped environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },
  {
    name: "stop_environment",
    description: "Stop a running environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },
  {
    name: "add_environment_variables",
    description: "Add or update environment variables",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
        variables: {
          type: "array",
          description: "Array of variables to add",
          items: {
            type: "object",
            properties: {
              key: { type: "string" },
              value: { type: "string" },
            },
            required: ["key", "value"],
          },
        },
        method: {
          type: "string",
          description: "Insert method: 'set' (update existing) or 'append' (add without checking)",
          enum: ["set", "append"],
          default: "set",
        },
      },
      required: ["environment_id", "variables"],
    },
  },

  // Deployments
  {
    name: "list_deployments",
    description: "List deployments for an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },
  {
    name: "get_deployment",
    description: "Get details of a specific deployment",
    inputSchema: {
      type: "object",
      properties: {
        deployment_id: {
          type: "string",
          description: "The deployment ID",
        },
      },
      required: ["deployment_id"],
    },
  },
  {
    name: "create_deployment",
    description: "Trigger a new deployment for an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },

  // Commands
  {
    name: "run_command",
    description: "Run an artisan or shell command on an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
        command: {
          type: "string",
          description: "The command to run (e.g., 'php artisan migrate')",
        },
      },
      required: ["environment_id", "command"],
    },
  },
  {
    name: "get_command",
    description: "Get the status and output of a command",
    inputSchema: {
      type: "object",
      properties: {
        command_id: {
          type: "string",
          description: "The command ID",
        },
      },
      required: ["command_id"],
    },
  },
  {
    name: "list_commands",
    description: "List recent commands for an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },

  // Instances
  {
    name: "list_instances",
    description: "List instances for an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },

  // Domains
  {
    name: "list_domains",
    description: "List domains for an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
      },
      required: ["environment_id"],
    },
  },
  {
    name: "create_domain",
    description: "Add a custom domain to an environment",
    inputSchema: {
      type: "object",
      properties: {
        environment_id: {
          type: "string",
          description: "The environment ID",
        },
        domain: {
          type: "string",
          description: "The domain name (e.g., 'app.example.com')",
        },
      },
      required: ["environment_id", "domain"],
    },
  },
  {
    name: "verify_domain",
    description: "Verify DNS configuration for a domain",
    inputSchema: {
      type: "object",
      properties: {
        domain_id: {
          type: "string",
          description: "The domain ID",
        },
      },
      required: ["domain_id"],
    },
  },

  // Database Clusters
  {
    name: "list_database_clusters",
    description: "List all database clusters",
    inputSchema: {
      type: "object",
      properties: {},
    },
  },

  // Caches
  {
    name: "list_caches",
    description: "List all cache instances (Redis)",
    inputSchema: {
      type: "object",
      properties: {},
    },
  },

  // Meta
  {
    name: "list_regions",
    description: "List available Laravel Cloud regions",
    inputSchema: {
      type: "object",
      properties: {},
    },
  },
  {
    name: "list_ip_addresses",
    description: "Get IP addresses to whitelist for database access",
    inputSchema: {
      type: "object",
      properties: {},
    },
  },
];

// Tool handlers
async function handleTool(name, args) {
  switch (name) {
    // Applications
    case "list_applications": {
      const response = await apiRequest("GET", "/applications");
      return formatResourceList(response);
    }
    case "get_application": {
      const response = await apiRequest("GET", `/applications/${args.application_id}`);
      return formatResource(response.data);
    }
    case "create_application": {
      const response = await apiRequest("POST", "/applications", {
        name: args.name,
        repository: args.repository,
        region: args.region,
      });
      return formatResource(response.data);
    }

    // Environments
    case "list_environments": {
      const response = await apiRequest("GET", `/applications/${args.application_id}/environments`);
      return formatResourceList(response);
    }
    case "get_environment": {
      const response = await apiRequest("GET", `/environments/${args.environment_id}`);
      return formatResource(response.data);
    }
    case "start_environment": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/start`);
      return formatResource(response.data);
    }
    case "stop_environment": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/stop`);
      return formatResource(response.data);
    }
    case "add_environment_variables": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/variables`, {
        method: args.method || "set",
        variables: args.variables,
      });
      return formatResource(response.data);
    }

    // Deployments
    case "list_deployments": {
      const response = await apiRequest("GET", `/environments/${args.environment_id}/deployments`);
      return formatResourceList(response);
    }
    case "get_deployment": {
      const response = await apiRequest("GET", `/deployments/${args.deployment_id}`);
      return formatResource(response.data);
    }
    case "create_deployment": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/deployments`);
      return formatResource(response.data);
    }

    // Commands
    case "run_command": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/commands`, {
        command: args.command,
      });
      return formatResource(response.data);
    }
    case "get_command": {
      const response = await apiRequest("GET", `/commands/${args.command_id}`);
      return formatResource(response.data);
    }
    case "list_commands": {
      const response = await apiRequest("GET", `/environments/${args.environment_id}/commands`);
      return formatResourceList(response);
    }

    // Instances
    case "list_instances": {
      const response = await apiRequest("GET", `/environments/${args.environment_id}/instances`);
      return formatResourceList(response);
    }

    // Domains
    case "list_domains": {
      const response = await apiRequest("GET", `/environments/${args.environment_id}/domains`);
      return formatResourceList(response);
    }
    case "create_domain": {
      const response = await apiRequest("POST", `/environments/${args.environment_id}/domains`, {
        domain: args.domain,
      });
      return formatResource(response.data);
    }
    case "verify_domain": {
      const response = await apiRequest("POST", `/domains/${args.domain_id}/verify`);
      return formatResource(response.data);
    }

    // Database Clusters
    case "list_database_clusters": {
      const response = await apiRequest("GET", "/database-clusters");
      return formatResourceList(response);
    }

    // Caches
    case "list_caches": {
      const response = await apiRequest("GET", "/caches");
      return formatResourceList(response);
    }

    // Meta
    case "list_regions": {
      return await apiRequest("GET", "/regions");
    }
    case "list_ip_addresses": {
      return await apiRequest("GET", "/ips");
    }

    default:
      throw new Error(`Unknown tool: ${name}`);
  }
}

// Create and run the server
const server = new Server(
  {
    name: "laravel-cloud",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

server.setRequestHandler(ListToolsRequestSchema, async () => {
  return { tools };
});

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    const result = await handleTool(name, args || {});
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(result, null, 2),
        },
      ],
    };
  } catch (error) {
    return {
      content: [
        {
          type: "text",
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("Laravel Cloud MCP server running on stdio");
}

main().catch(console.error);
