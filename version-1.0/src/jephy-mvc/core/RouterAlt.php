<?php
namespace App\Core;
use Exception;
class RouterAlt
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
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
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
                // Execute before routing hook
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
            
            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Execute before routing hook
                $this->hooks->exec('beforeRoute', [
                    'route' => $route,
                    'uri' => $uri,
                    'method' => $method
                ]);
                
                return [
                    'controllerAction' => $route['controllerAction'],
                    'params' => $matches
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
    
    private function convertToRegex($path)
    {
        // Handle root path
        if ($path === '/') {
            return '#^/$#';
        }
        
        // Convert route parameters to regex patterns
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
