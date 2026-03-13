<?php
namespace App\Core;
use Exception;

class Router
{
    private $routes = [];
    private $hooks;
    
    public function __construct(HookManager $hooks)
    {
        $this->hooks = $hooks;
    }
    
    public function addRoute($method, $path, $controllerAction)
    {
        // Normalize path - remove trailing slash for consistency
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        
        // Compile regex pattern for this route
        $pattern = $this->compilePattern($path);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern['regex'],
            'paramNames' => $pattern['paramNames'],
            'controllerAction' => $controllerAction
        ];
    }
    
    public function route($uri, $method)
    {
        // Parse the URI to remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Normalize the URI
        $uri = $this->normalizeUri($uri);
        
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
                    'controllerAction' => $route['controllerAction'],
                    'params' => []
                ];
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Remove full match (index 0)
                array_shift($matches);
                
                // Create named parameters array
                $params = [];
                foreach ($route['paramNames'] as $index => $name) {
                    if (isset($matches[$index])) {
                        $params[$name] = $matches[$index];
                    }
                }
                
                $this->hooks->exec('beforeRoute', [
                    'route' => $route,
                    'uri' => $uri,
                    'method' => $method
                ]);
                
                return [
                    'controllerAction' => $route['controllerAction'],
                    'params' => $params
                ];
            }
        }
        
        throw new Exception('Route not found: ' . $uri . ' (' . $method . ')');
    }
    
    private function normalizeUri($uri)
    {
        // Remove trailing slashes
        $uri = rtrim($uri, '/');
        
        // If empty after trimming, set to root
        if ($uri === '') {
            $uri = '/';
        }
        
        return $uri;
    }
    
    private function compilePattern($path)
    {
        $paramNames = [];
        
        // Convert route pattern to regex
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/',
            function($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $path
        );
        
        // Escape forward slashes
        $regex = str_replace('/', '\/', $regex);
        
        return [
            'regex' => '#^' . $regex . '$#',
            'paramNames' => $paramNames
        ];
    }
    
    /**
     * Get all registered routes for debugging
     */
    public function getRoutes()
    {
        return $this->routes;
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
                $route['controllerAction'] ?? 'HomeController@index'
            );
        }
    }
	
}

