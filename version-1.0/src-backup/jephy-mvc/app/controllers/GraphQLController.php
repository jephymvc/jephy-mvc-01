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

		// Log every request for debugging
		error_log('========== GRAPHQL HANDLE CALLED ==========');
		error_log('Method: ' . $_SERVER['REQUEST_METHOD']);
		error_log('URI: ' . $_SERVER['REQUEST_URI']);
		error_log('Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
		
		try {
			// Handle CORS preflight
			if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
				$this->handleCORS();
				return;
			}

			// Set CORS headers for all responses
			$this->handleCORS();

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
			error_log('Raw input: ' . $rawInput);

			if (empty($rawInput)) {
				return $this->json(['error' => 'Empty request body'], 400);
			}

			// Try to decode JSON
			$input = json_decode($rawInput, true);
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				error_log('JSON decode error: ' . json_last_error_msg());
				return $this->json([
					'error' => 'Invalid JSON',
					'details' => json_last_error_msg(),
					'raw_input' => substr($rawInput, 0, 200) // Show first 200 chars
				], 400);
			}

			error_log('Decoded input: ' . print_r($input, true));

			$query = $input['query'] ?? '';
			$variables = $input['variables'] ?? [];

			if (empty($query)) {
				return $this->json(['error' => 'No query provided'], 400);
			}

			// Return success response
			return $this->json([
				'success' => true,
				'message' => 'GraphQL endpoint is working',
				'received_query' => substr($query, 0, 100) . '...',
				'received_variables' => $variables
			]);

		} catch ( \Exception $e ) {
			error_log('GraphQL Exception: ' . $e->getMessage());
			error_log('Exception trace: ' . $e->getTraceAsString());
			
			return $this->json( [
				'errors' => [ [
					'message' => $e->getMessage(),
					'trace' => $e->getTraceAsString()
				] ]
			], 500 );
		}
	}

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

            $query 		= $input['query'] ?? '';
            $variables 	= $input['variables'] ?? [];

            if (empty($query)) {
                throw new \Exception('No query provided');
            }

            $context = [
                'framework' => Framework::getInstance(),
                'controller' => $this,
                'user' => null,
                'session' => $_SESSION['user_session'] ?? []
            ];

            $result = $this->graphql->execute($query, $variables, $context);

            $this->json($result);

        } catch (\Exception $e) {
            error_log('GraphQL Error: ' . $e->getMessage());
            $this->json(['errors' => [['message' => $e->getMessage()]]], 500);
        }
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
		$siteName 	= $config->get( 'site.name', 'GraphQL' ); // Default to 'GraphQL' if not set
		$endpoint 	= $config->get( 'graphql.endpoint', '/graphql' );
		$theme 		= $config->get( 'graphql.playground_theme', 'dark' );
		
		
		header('Content-Type: text/html; charset=utf-8');
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo htmlspecialchars($siteName); ?> - GraphQL Playground</title>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphiql@3.0.0/graphiql.min.css" />
			<script src="https://cdn.jsdelivr.net/npm/react@18.2.0/umd/react.production.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/react-dom@18.2.0/umd/react-dom.production.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/graphiql@3.0.0/graphiql.min.js"></script>
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
			<script>
				// GraphQL fetcher function
				function graphQLFetcher(graphQLParams) {
					return fetch('<?php echo $endpoint; ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify(graphQLParams),
						credentials: 'include'
					})
					.then(function(response) {
						if (!response.ok) {
							throw new Error('HTTP ' + response.status);
						}
						return response.json();
					})
					.catch(function(error) {
						console.error('Fetch error:', error);
						return {
							errors: [{
								message: error.message || 'Failed to fetch from GraphQL endpoint'
							}]
						};
					});
				}

				// Default query
				const defaultQuery = `# Welcome to GraphQL Playground
				#
				# Type your query here:

				{
				  users {
					id
					username
					email
				  }
				}

				# Example mutation:
				# mutation {
				#   createPost(title: "Hello", content: "World") {
				#     id
				#     title
				#   }
				# }`;

				// Initialize GraphiQL
				ReactDOM.render(
					React.createElement(GraphiQL, {
						fetcher: graphQLFetcher,
						defaultQuery: defaultQuery,
						editorTheme: 'dark',
						headerEditorEnabled: true
					}),
					document.getElementById('root')
				);
			</script>
		</body>
		</html>
		<?php
	}
	
	private function renderPlaygroundAlt2()
	{
		
		$config 	= \App\Core\Config::getInstance();
		$siteName 	= $config->get( 'site.name', 'GraphQL' ); // Default to 'GraphQL' if not set
		$endpoint 	= $config->get( 'graphql.endpoint', '/graphql' );
		$theme 		= $config->get( 'graphql.playground_theme', 'dark' );
		
		header('Content-Type: text/html; charset=utf-8');
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo htmlspecialchars($siteName); ?> - GraphQL IDE</title>
			
			<!-- GraphiQL CSS -->
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphiql@3.0.0/graphiql.min.css" />
			
			<!-- React and ReactDOM -->
			<script src="https://cdn.jsdelivr.net/npm/react@18.2.0/umd/react.production.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/react-dom@18.2.0/umd/react-dom.production.min.js"></script>
			
			<!-- GraphiQL JS -->
			<script src="https://cdn.jsdelivr.net/npm/graphiql@3.0.0/graphiql.min.js"></script>
			
			<style>
				body, html, #root {
					height: 100%;
					margin: 0;
					padding: 0;
					width: 100%;
					overflow: hidden;
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
				}
				
				.error-message {
					padding: 20px;
					color: #ff6b6b;
					background: #2d2d2d;
					border-radius: 5px;
					margin: 20px;
				}
			</style>
		</head>
		<body>
			<div id="root">
				<div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
					<div style="text-align: center;">
						<h2>Loading GraphQL IDE...</h2>
						<div class="spinner"></div>
					</div>
				</div>
			</div>

			<script>
				(function() {
					// Wait for everything to load
					function initializeGraphiQL() {
						const root = document.getElementById('root');
						
						// Check if all dependencies are loaded
						if (typeof React === 'undefined' || 
							typeof ReactDOM === 'undefined' || 
							typeof GraphiQL === 'undefined') {
							
							console.error('Missing dependencies:', {
								React: typeof React,
								ReactDOM: typeof ReactDOM,
								GraphiQL: typeof GraphiQL
							});
							
							root.innerHTML = `
								<div style="padding: 20px; color: red;">
									<h3>Failed to load GraphQL IDE</h3>
									<p>Missing dependencies. Check console for details.</p>
								</div>
							`;
							return;
						}

						// GraphQL fetcher function
						function graphQLFetcher(graphQLParams) {
							return fetch('<?php echo $endpoint; ?>', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'Accept': 'application/json',
								},
								body: JSON.stringify(graphQLParams),
								credentials: 'include',
							})
							.then(function(response) {
								if (!response.ok) {
									throw new Error('HTTP ' + response.status);
								}
								return response.json();
							})
							.catch(function(error) {
								console.error('Fetch error:', error);
								return {
									errors: [{
										message: error.message || 'Failed to fetch from GraphQL endpoint'
									}]
								};
							});
						}

						// Default query
						const defaultQuery = `# Welcome to GraphQL IDE
	#
	# Type your query here:

	{
	  users {
		id
		username
		email
	  }
	}

	# Example mutation:
	# mutation {
	#   createPost(title: "Hello", content: "World") {
	#     id
	#     title
	#   }
	# }`;

						// Create GraphiQL component
						try {
							const graphiQL = React.createElement(GraphiQL, {
								fetcher: graphQLFetcher,
								defaultQuery: defaultQuery,
								editorTheme: '<?php echo $theme; ?>',
								headerEditorEnabled: true,
								shouldPersistHeaders: true
							});

							// Render
							ReactDOM.render(graphiQL, root);
							console.log('GraphQL IDE loaded successfully');
						} catch (error) {
							console.error('Failed to render GraphiQL:', error);
							root.innerHTML = `
								<div style="padding: 20px; color: red;">
									<h3>Failed to render GraphQL IDE</h3>
									<p>${error.message}</p>
								</div>
							`;
						}
					}

					// Try to initialize immediately
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', initializeGraphiQL);
					} else {
						initializeGraphiQL();
					}
				})();
			</script>

			<style>
				.spinner {
					border: 4px solid #f3f3f3;
					border-top: 4px solid #3498db;
					border-radius: 50%;
					width: 40px;
					height: 40px;
					animation: spin 1s linear infinite;
					margin: 20px auto;
				}
				
				@keyframes spin {
					0% { transform: rotate(0deg); }
					100% { transform: rotate(360deg); }
				}
			</style>
		</body>
		</html>
		<?php
	}
	
	private function renderPlaygroundAlt1()
	{
		
		$config 	= \App\Core\Config::getInstance();
		$siteName 	= $config->get( 'site.name', 'GraphQL' ); // Default to 'GraphQL' if not set
		$endpoint 	= $config->get( 'graphql.endpoint', '/graphql' );
		$theme 		= $config->get( 'graphql.playground_theme', 'dark' );
		
		// Add a default query to show
		$defaultQuery = <<<'GRAPHQL'
		# Welcome to GraphQL Playground
		# Type your query here

		query {
		  users {
			id
			username
			email
		  }
		}

		# Or try a mutation:
		# mutation {
		#   createPost(title: "Hello", content: "World") {
		#     id
		#     title
		#   }
		# }
		GRAPHQL;

		header('Content-Type: text/html; charset=utf-8');
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, minimal-ui">
			<title><?php echo htmlspecialchars($siteName); ?> Playground</title>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.42/build/static/css/index.css">
			<link rel="shortcut icon" href="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.42/build/favicon.png">
			<script src="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.42/build/static/js/middleware.js"></script>
		</head>
		<body>
			<div id="root"></div>
			<script>
				window.addEventListener('load', function() {
					// Mount GraphQL Playground
					GraphQLPlayground.init(document.getElementById('root'), {
						// Essential settings
						endpoint: '<?php echo $endpoint; ?>',
						
						// Optional: subscription endpoint if you have subscriptions
						// subscriptionEndpoint: 'ws://localhost/graphql',
						
						// Workspace settings
						settings: {
							'request.credentials': 'include',
							'editor.theme': '<?php echo $theme; ?>',
							'editor.cursorShape': 'line',
							'editor.fontSize': 14,
							'editor.fontFamily': 'Fira Code, Consolas, monospace',
							'editor.reuseHeaders': true,
							'prettier.printWidth': 80,
							'prettier.tabWidth': 2,
							'prettier.useTabs': false,
							'tracing.hideTracingResponse': true,
							'queryPlan.hideQueryPlanResponse': true
						},
						
						// Initial tab configuration
						tabs: [
							{
								endpoint: '<?php echo $endpoint; ?>',
								query: <?php echo json_encode($defaultQuery); ?>,
								name: 'Example Query',
								responses: []
							}
						]
					});
				});
			</script>
		</body>
		</html>
		<?php
	}

    private function renderPlaygroundAlt()
    {
		
		$config 	= \App\Core\Config::getInstance();
		$siteName 	= $config->get( 'site.name', 'GraphQL' ); // Default to 'GraphQL' if not set
		$endpoint 	= $config->get( 'graphql.endpoint', '/graphql' );
		$theme 		= $config->get( 'graphql.playground_theme', 'dark' );
	
		?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, minimal-ui">
            <title><?php echo htmlspecialchars( $siteName ); ?> Playground</title>           
			
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css" />
			<link rel="shortcut icon" href="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/favicon.png" />
			<script src="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
			
			
        </head>
        <body>
            <div id="root"></div>
            <script>
                window.addEventListener( 'load', function() {
                    GraphQLPlayground.init(document.getElementById('root'), {
                        endpoint: '<?php echo $endpoint; ?>',
                        settings: { 
							'request.credentials': 'include',
							'editor.theme': '<?php echo $theme; ?>'	
						}
                    });
                } );
            </script>
        </body>
        </html>
        <?php
		
    }
	
	
	

	private function loadPermissions()
	{
		$permissionsClass = 'App\\Permissions\\Permissions';		
		if (class_exists($permissionsClass) && method_exists($permissionsClass, 'define')) {
			$shield = $permissionsClass::define();
			$this->graphql->setShield($shield);
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

	private function getUserRoles($user)
	{
		if (!$user) return ['GUEST'];
		return isset($user['roles']) ? explode(',', $user['roles']) : ['USER'];
	}

	private function getUserPermissions($user)
	{
		if (!$user) return [];
		return isset($user['permissions']) ? explode(',', $user['permissions']) : [];
	}
	
    private function handleCORS()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
    }
	
	public function test()
	{
		return $this->json([
			'status' 	=> 'ok',
			'message' 	=> 'GraphQL endpoint is working',
			'time' 		=> date('Y-m-d H:i:s')
		]);
	}
	
	public function debug()
	{
		// Get all registered routes from router
		$router = Framework::getRouter();
		
		// You'll need to add this method to your Router class
		$routes = $router->getRoutes(); // We'll create this method
		
		return $this->json([
			'routes' => $routes,
			'current_url' => $_SERVER['REQUEST_URI'],
			'method' => $_SERVER['REQUEST_METHOD'],
			'graphql_route_exists' => $this->routeExists('/graphql', 'POST'),
			'test_route_exists' => $this->routeExists('/graphql/test', 'GET'),
			'playground_route_exists' => $this->routeExists('/graphql/playground', 'GET')
		]);
	}

	private function routeExists($path, $method)
	{
		$router = Framework::getRouter();
		$routes = $router->getRoutes();
		
		foreach ($routes as $route) {
			if ($route['path'] === $path && $route['method'] === $method) {
				return true;
			}
		}
		return false;
	}
}

