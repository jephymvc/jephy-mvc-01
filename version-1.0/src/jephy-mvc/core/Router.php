<?php
namespace App\Core;

use Exception;

class Router
{
    private $routes = [];
    private $hooks;
    
    // Properties to support route grouping
    private $routePrefix = '';
    private $prefixStack = [];
    private $namedRoutes = [];
    
    public function __construct(HookManager $hooks)
    {
        $this->hooks = $hooks;
    }
    
    /**
     * Register a GET route
     */
    public static function get(string $path, $handler): void
    {
        self::getRouterInstance()->addRoute('GET', $path, $handler);
    }
    
    /**
     * Register a POST route
     */
    public static function post(string $path, $handler): void
    {
        self::getRouterInstance()->addRoute('POST', $path, $handler);
    }
    
    /**
     * Register a PUT route
     */
    public static function put(string $path, $handler): void
    {
        self::getRouterInstance()->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Register a PATCH route
     */
    public static function patch(string $path, $handler): void
    {
        self::getRouterInstance()->addRoute('PATCH', $path, $handler);
    }
    
    /**
     * Register a DELETE route
     */
    public static function delete(string $path, $handler): void
    {
        self::getRouterInstance()->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Register a route that responds to any HTTP method
     */
    public static function any(string $path, $handler): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        foreach ($methods as $method) {
            self::getRouterInstance()->addRoute($method, $path, $handler);
        }
    }
    
    /**
     * Register a route that responds to multiple specified methods
     */
    public static function match(array $methods, string $path, $handler): void
    {
        foreach ($methods as $method) {
            self::getRouterInstance()->addRoute(strtoupper($method), $path, $handler);
        }
    }
    
    /**
     * Register API resource routes for a controller
     */
    public static function apiResource(string $resource, $controller): void
    {
        $basePath = rtrim($resource, '/');
        $basePath = $basePath === '' ? '/' : $basePath;
        
        // Convert controller to string if it's a class string
        $controllerStr = is_string($controller) ? $controller : get_class($controller);
        
        self::get($basePath, self::formatHandler($controllerStr, 'index'));
        self::post($basePath, self::formatHandler($controllerStr, 'store'));
        self::get($basePath . '/{id}', self::formatHandler($controllerStr, 'show'));
        self::put($basePath . '/{id}', self::formatHandler($controllerStr, 'update'));
        self::patch($basePath . '/{id}', self::formatHandler($controllerStr, 'update'));
        self::delete($basePath . '/{id}', self::formatHandler($controllerStr, 'destroy'));
    }
    
    /**
     * Format handler string based on syntax
     */
    private static function formatHandler(string $controller, string $method): string
    {
        // Check if using :: syntax is preferred
        $config = Framework::getInstance()->getConfig();
        $preferStaticSyntax = $config->get('router.prefer_static_syntax', false);
        
        return $preferStaticSyntax ? $controller . '::' . $method : $controller . '@' . $method;
    }
    
    /**
     * Register multiple API resource routes at once
     */
    public static function apiResources(array $resources): void
    {
        foreach ($resources as $resource => $controller) {
            self::apiResource($resource, $controller);
        }
    }
    
    /**
     * Register a route with optional name for URL generation
     */
    public static function named(string $name, string $method, string $path, $handler): void
    {
        $router = self::getRouterInstance();
        $router->addRoute($method, $path, $handler);
        $router->addNamedRoute($name, $path, $method);
    }
    
    /**
     * Group routes with a common prefix
     */
    public static function group(string $prefix, callable $callback): void
    {
        $router = self::getRouterInstance();
        $router->pushRoutePrefix($prefix);
        $callback();
        $router->popRoutePrefix();
    }
    
    /**
     * Route redirection
     */
    public static function redirect(string $from, string $to, int $statusCode = 302): void
    {
        self::any($from, function() use ($to, $statusCode) {
            http_response_code($statusCode);
            header("Location: $to");
            exit;
        });
    }
    
    /**
     * View route - directly returns a view using closure
     */
    public static function view(string $path, string $view, array $data = []): void
    {
        self::get($path, function() use ($view, $data) {
            return view($view, $data);
        });
    }
    
    /**
     * Get the router instance from Framework
     */
    private static function getRouterInstance(): self
    {
        return Framework::getRouter();
    }
    
    /**
     * Push a route prefix onto the stack
     */
    public function pushRoutePrefix(string $prefix): void
    {
        $this->prefixStack[] = $this->routePrefix;
        $this->routePrefix = rtrim($this->routePrefix . '/' . trim($prefix, '/'), '/');
        if ($this->routePrefix === '') {
            $this->routePrefix = '/';
        }
    }
    
    /**
     * Pop the route prefix from the stack
     */
    public function popRoutePrefix(): void
    {
        $this->routePrefix = array_pop($this->prefixStack) ?? '';
    }
    
    /**
     * Get the current route prefix
     */
    public function getRoutePrefix(): string
    {
        return $this->routePrefix;
    }
    
    /**
     * Add a named route
     */
    public function addNamedRoute(string $name, string $path, string $method = 'GET'): void
    {
        $this->namedRoutes[$name] = [
            'path' => $path,
            'method' => $method
        ];
    }
    
    /**
     * Generate URL for a named route
     */
    public static function url(string $name, array $params = []): string
    {
        $router = self::getRouterInstance();
        
        if (!isset($router->namedRoutes[$name])) {
            throw new Exception("Named route '$name' not found");
        }
        
        $path = $router->namedRoutes[$name]['path'];
        
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }
        
        return $path;
    }
    
    /**
     * Add a route with support for multiple handler types
     */
    public function addRoute($method, $path, $handler)
    {
        // Apply route prefix if set
        if ($this->routePrefix !== '' && $this->routePrefix !== '/') {
            $path = $this->routePrefix . '/' . ltrim($path, '/');
        }
        
        // Normalize path
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        
        // Compile regex pattern
        $pattern = $this->compilePattern($path);
        
        // Determine handler type
        $handlerInfo = $this->parseHandler($handler);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern['regex'],
            'paramNames' => $pattern['paramNames'],
            'handler' => $handler,
            'handlerType' => $handlerInfo['type'],
            'controller' => $handlerInfo['controller'] ?? null,
            'action' => $handlerInfo['action'] ?? null,
            'isStatic' => $handlerInfo['isStatic'] ?? false
        ];
    }
    
    /**
     * Parse handler to determine type and extract controller/action
     */
    private function parseHandler($handler): array
    {
        // Closure handler
        if ($handler instanceof \Closure) {
            return [
                'type' => 'closure',
                'handler' => $handler
            ];
        }
        
        // Callable array [new Controller(), 'method']
        if (is_array($handler) && count($handler) === 2 && is_object($handler[0])) {
            return [
                'type' => 'callable_array',
                'controller' => $handler[0],
                'action' => $handler[1],
                'isStatic' => false
            ];
        }
        
        // Callable array ['Controller', 'method'] - static call
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            return [
                'type' => 'static_callable',
                'controller' => $handler[0],
                'action' => $handler[1],
                'isStatic' => true
            ];
        }
        
        // String handler
        if (is_string($handler)) {
            // Check for static syntax Controller::method
            if (strpos($handler, '::') !== false) {
                $parts = explode('::', $handler);
                return [
                    'type' => 'static_method',
                    'controller' => $parts[0],
                    'action' => $parts[1],
                    'isStatic' => true
                ];
            }
            
            // Check for instance syntax Controller@method
            if (strpos($handler, '@') !== false) {
                $parts = explode('@', $handler);
                return [
                    'type' => 'instance_method',
                    'controller' => $parts[0],
                    'action' => $parts[1],
                    'isStatic' => false
                ];
            }
        }
        
        // Unknown type
        return [
            'type' => 'unknown',
            'handler' => $handler
        ];
    }
    
    /**
     * Route the request to the appropriate handler
     */
    public function route($uri, $method)
    {
        // Parse the URI to remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Normalize the URI
        $uri = $this->normalizeUri($uri);
        
        // Get query parameters
        $query = [];
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '', $query);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            // Special case for root path
            if ($route['path'] === '/' && $uri === '/') {
                $this->hooks->exec('beforeRoute', [
                    'route' => $route,
                    'uri' => $uri,
                    'method' => $method
                ]);
                
                return [
                    'handler' => $route['handler'],
                    'handlerType' => $route['handlerType'],
                    'controller' => $route['controller'] ?? null,
                    'action' => $route['action'] ?? null,
                    'isStatic' => $route['isStatic'] ?? false,
                    'params' => [],
                    'query' => $query
                ];
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Remove full match
                array_shift($matches);
                
                // Create named parameters array
                $params = [];
                foreach ($route['paramNames'] as $index => $name) {
                    if (isset($matches[$index])) {
                        $params[$name] = $matches[$index];
                    }
                }
                
                // Merge with query parameters
                $params = array_merge($params, $query);
                
                $this->hooks->exec('beforeRoute', [
                    'route' => $route,
                    'uri' => $uri,
                    'method' => $method
                ]);
                
                return [
                    'handler' => $route['handler'],
                    'handlerType' => $route['handlerType'],
                    'controller' => $route['controller'] ?? null,
                    'action' => $route['action'] ?? null,
                    'isStatic' => $route['isStatic'] ?? false,
                    'params' => $params,
                    'query' => $query
                ];
            }
        }
        
        // No route found - return 404 handler
        return [
            'handler' => 'ErrorController@index',
            'handlerType' => 'instance_method',
            'controller' => 'ErrorController',
            'action' => 'index',
            'isStatic' => false,
            'params' => ['uri' => $uri, 'method' => $method],
            'query' => $query
        ];
    }
    
    private function normalizeUri($uri)
    {
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }
        return $uri;
    }
    
    private function compilePattern($path)
    {
        $paramNames = [];
        
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/',
            function($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $path
        );
        
        $regex = str_replace('/', '\/', $regex);
        
        return [
            'regex' => '#^' . $regex . '$#',
            'paramNames' => $paramNames
        ];
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Get all named routes
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }
    
    /**
     * Add multiple routes at once
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute(
                $route['method'] ?? 'GET',
                $route['path'] ?? '/',
                $route['handler'] ?? $route['controllerAction'] ?? 'HomeController@index'
            );
        }
    }
}


#	// In your routes.php file or bootstrap
#	use App\Core\Router;
#	
#	// Simple routes
#	Router::get('/', 'HomeController@index');
#	Router::post('/contact', 'ContactController@submit');
#	Router::put('/users/{id}', 'UserController@update');
#	
#	// Routes with any method
#	Router::any('/webhook', 'WebhookController@handle');
#	
#	// API Resource routes
#	Router::apiResource('users', 'UserController');
#	Router::apiResource('posts', 'PostController');
#	
#	// Multiple resources at once
#	Router::apiResources([
#	    'products' => 'ProductController',
#	    'orders' => 'OrderController',
#	    'customers' => 'CustomerController'
#	]);
#	
#	// Route groups with prefix
#	Router::group('/admin', function() {
#	    Router::get('/dashboard', 'AdminController@dashboard');
#	    Router::get('/users', 'AdminController@users');
#	    Router::post('/users/{id}', 'AdminController@updateUser');
#	});
#	
#	// Named routes (for URL generation)
#	Router::named('user.profile', 'GET', '/users/{id}', 'UserController@profile');
#	Router::named('user.edit', 'GET', '/users/{id}/edit', 'UserController@edit');
#	
#	// Generate URLs for named routes
#	$profileUrl = Router::url('user.profile', ['id' => 123]); // Returns: /users/123
#	
#	// Redirect routes
#	Router::redirect('/old-page', '/new-page', 301);
#	
#	// View routes (directly render Smarty templates)
#	Router::view('/about', 'pages/about', ['title' => 'About Us']);
#	Router::view('/contact', 'pages/contact', ['title' => 'Contact']);
#	
#	// Route with multiple methods
#	Router::match(['GET', 'POST'], '/profile', 'ProfileController@handle');
#	