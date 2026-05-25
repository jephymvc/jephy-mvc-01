<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Framework;
use App\Core\GraphQL\GraphQL;
use App\Core\GraphQL\TypeRegistry;
use App\Core\GraphQL\GraphQLAutoLoader;

class GraphQLController extends Controller
{
    use GraphQLAutoLoader;
    
    private $graphql;
    private $initialized = false;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->graphql = GraphQL::getInstance($this->appPath);
    }

    private function initializeGraphQL()
    {
        if ($this->initialized) {
            return;
        }

        $this->autoDiscoverGraphQLTypes();
        $this->autoDiscoverGraphQLQueries($this->graphql);
        $this->autoDiscoverGraphQLMutations($this->graphql);
        
        // Load and set permissions
        $this->loadPermissions();        
        $this->initialized = true;
    }
    
    public function handle()
    {
        // Clear all output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Handle CORS first for all requests
        $this->handleCORS();
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        try {
            // Initialize GraphQL
            $this->initializeGraphQL();

            // Handle GET requests - show info
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return $this->json([
                    'message' => 'GraphQL endpoint is ready. Send POST requests with GraphQL queries.',
                    'test_endpoint' => '/graphql/test',
                    'playground' => '/graphql/playground',
                    'debug_endpoint' => '/graphql/debug'
                ]);
            }

            // Only POST for actual queries
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->json(['error' => 'Method not allowed. Use POST'], 405);
            }

            // Get raw input
            $rawInput = file_get_contents('php://input');
            
            if (empty($rawInput)) {
                return $this->json(['error' => 'Empty request body'], 400);
            }

            // Decode JSON
            $input = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => 'Invalid JSON',
                    'details' => json_last_error_msg()
                ], 400);
            }

            $query = $input['query'] ?? '';
            $variables = $input['variables'] ?? [];

            if (empty($query)) {
                return $this->json(['error' => 'No query provided'], 400);
            }

            // Build context and execute GraphQL query
            $context = $this->buildGraphQLContext();
            $result = $this->graphql->execute($query, $variables, $context);

            // Return the actual GraphQL result
            return $this->json($result);

        } catch (\Exception $e) {
            error_log('GraphQL Exception: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'errors' => [[
                    'message' => $e->getMessage()
                ]]
            ], 500);
        }
    }

    // Keep handleAlt as a backup or remove it
    public function handleAlt()
    {
        try {
            $this->initializeGraphQL();

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                $this->handleCORS();
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['error' => 'Method not allowed'], 405);
                return;
            }

            $this->handleCORS();

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }

            $query = $input['query'] ?? '';
            $variables = $input['variables'] ?? [];

            if (empty($query)) {
                throw new \Exception('No query provided');
            }

            $context = $this->buildGraphQLContext();
            $result = $this->graphql->execute($query, $variables, $context);

            $this->json($result);

        } catch (\Exception $e) {
            error_log('GraphQL Error: ' . $e->getMessage());
            $this->json(['errors' => [['message' => $e->getMessage()]]], 500);
        }
    }

    private function loadPermissions()
    {
        $permissionsClass = 'App\\Permissions\\Permissions';
        
        // Check if permissions class exists and has define method
        if (class_exists($permissionsClass) && method_exists($permissionsClass, 'define')) {
            try {
                $shield = $permissionsClass::define();
                if ($shield !== null) {
                    $this->graphql->setShield($shield);
                }
            } catch (\Exception $e) {
                error_log('Failed to load permissions: ' . $e->getMessage());
            }
        }
    }

    private function buildGraphQLContext()
    {
        $user = $this->getCurrentUser();
        
        return [
            'framework' => Framework::getInstance(),
            'controller' => $this,
            'user' => $user ? new \App\Core\GraphQL\Shield\ShieldUser($user) : null,
            'userId' => $user ? ($user['id'] ?? null) : null,
            'userRoles' => $this->getUserRoles($user),
            'userPermissions' => $this->getUserPermissions($user),
            'session' => $_SESSION['user_session'] ?? []
        ];
    }

    // Add this missing method
    private function getCurrentUser()
    {
        // Implement your user authentication logic here
        // Example:
        if (isset($_SESSION['user_session']) && !empty($_SESSION['user_session'])) {
            // Get user from session or database
            // Return user array/object or null if not found
            return $_SESSION['user_session']['user'] ?? null;
        }
        
        return null; // No user logged in
    }

    private function getUserRoles($user)
    {
        if (!$user) return ['GUEST'];
        return isset($user['roles']) ? (is_array($user['roles']) ? $user['roles'] : explode(',', $user['roles'])) : ['USER'];
    }

    private function getUserPermissions($user)
    {
        if (!$user) return [];
        return isset($user['permissions']) ? (is_array($user['permissions']) ? $user['permissions'] : explode(',', $user['permissions'])) : [];
    }
    
    private function handleCORS()
    {
        // Allow from any origin (you might want to restrict this)
        $allowedOrigins = ['*']; // Change this to specific domains in production
        
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $origin = $_SERVER['HTTP_ORIGIN'];
            if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: $origin");
            }
        }
        
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
    }
    
    public function test()
    {
        $this->handleCORS();
        return $this->json([
            'status' => 'ok',
            'message' => 'GraphQL endpoint is working',
            'time' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function debug()
    {
        $this->handleCORS();
        
        // Get all registered routes from router
        $router = Framework::getRouter();
        
        // Check if method exists before calling
        $routes = method_exists($router, 'getRoutes') ? $router->getRoutes() : [];
        
        return $this->json([
            'routes' => $routes,
            'current_url' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'graphql_ready' => $this->initialized,
            'has_queries' => property_exists($this->graphql, 'queries') && count($this->graphql->queries) > 0,
            'php_version' => PHP_VERSION,
            'graphql_library_loaded' => class_exists('GraphQL\GraphQL')
        ]);
    }

    public function playground()
    {
        $debugMode = Framework::getInstance()->getAppDebugMode();

        if (!$debugMode) {
            http_response_code(404);
            echo 'GraphQL Playground is only available in debug mode';
            return;
        }

        $this->initializeGraphQL();
        $this->renderPlayground();
    }
    
	private function renderPlayground()
	{
		$config 	= \App\Core\Config::getInstance();
		$siteName 	= $config->get('site.name', 'GraphQL');
		$endpoint 	= $config->get('graphql.endpoint', '/graphql');
		$theme 		= $config->get('graphql.playground_theme', 'dark');
		
		header('Content-Type: text/html; charset=utf-8');
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo htmlspecialchars($siteName); ?> - GraphQL Playground</title>
			<link rel="stylesheet" href="https://unpkg.com/graphiql@3.0.6/graphiql.min.css" />
			<style>
				body, html, #root {
					height: 100%;
					margin: 0;
					padding: 0;
					width: 100%;
					overflow: hidden;
				}
			</style>
		</head>
		<body>
			<div id="root"></div>
			
			<!-- Load React and ReactDOM first -->
			<script src="https://unpkg.com/react@18.2.0/umd/react.development.js"></script>
			<script src="https://unpkg.com/react-dom@18.2.0/umd/react-dom.development.js"></script>
			
			<!-- Then load GraphiQL -->
			<script src="https://unpkg.com/graphiql@3.0.6/graphiql.min.js"></script>
			
			<script>
				// This function will be called by GraphiQL to execute queries
				function graphQLFetcher(graphQLParams) {
					return fetch('<?php echo $endpoint; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify(graphQLParams),
						credentials: 'include'
					}).then(response => response.json());
				}

				// Default query to show
				const defaultQuery = `# Welcome to GraphQL Playground
	# Test your queries here

	query {
	  # Example: Get users
	  # users {
	  #   id
	  #   username
	  #   email
	  # }
	  
	  # Add your query here
	  __schema {
		types {
		  name
		}
	  }
	}`;

				// Wait for everything to load
				window.addEventListener('load', function() {
					// Create the GraphiQL component
					const graphiql = React.createElement(GraphiQL, {
						fetcher: graphQLFetcher,
						defaultQuery: defaultQuery,
						editorTheme: '<?php echo $theme; ?>'
					});
					
					// Render it to the DOM
					ReactDOM.render(graphiql, document.getElementById('root'));
				});
			</script>
		</body>
		</html>
		<?php
	}

}