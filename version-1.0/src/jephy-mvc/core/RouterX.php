<?php
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
        // Normalize path
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        
        // Extract parameter names and create regex pattern
        $paramNames = [];
        $pattern = $this->buildPattern($path, $paramNames);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controllerAction' => $controllerAction,
            'paramNames' => $paramNames,
            'pattern' => $pattern
        ];
    }
    
    public function route($uri, $method)
    {
        // Parse URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = $this->normalizeUri($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if ($route['path'] === '/' && $uri === '/') {
                return $this->executeRoute($route, $uri, $method, []);
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = [];
                foreach ($route['paramNames'] as $paramName) {
                    if (isset($matches[$paramName])) {
                        $params[$paramName] = $matches[$paramName];
                    }
                }
                
                return $this->executeRoute($route, $uri, $method, $params);
            }
        }
        
        throw new Exception('Route not found: ' . $uri . ' (' . $method . ')');
    }
    
    private function executeRoute($route, $uri, $method, $params)
    {
        // Execute before routing hook
        $this->hooks->exec('beforeRoute', [
            'route' => $route,
            'uri' => $uri,
            'method' => $method,
            'params' => $params
        ]);
        
        return [
            'controllerAction' => $route['controllerAction'],
            'params' => $params
        ];
    }
    
    private function normalizeUri($uri)
    {
        $uri = rtrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }
    
    private function buildPattern($path, &$paramNames)
    {
        if ($path === '/') {
            return '#^/$#';
        }
        
        // Escape forward slashes
        $pattern = preg_quote($path, '#');
        
        // Replace {param} with named capture groups
        $pattern = preg_replace_callback(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/',
            function($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '(?P<' . $matches[1] . '>[^\/]+)';
            },
            $pattern
        );
        
        return '#^' . $pattern . '$#';
    }
    
    // Optional: Add support for optional parameters
    public function addRouteWithOptional($method, $path, $controllerAction)
    {
        // Handle optional parameters like /user/{id?}
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        
        $paramNames = [];
        $pattern = $this->buildPatternWithOptional($path, $paramNames);
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controllerAction' => $controllerAction,
            'paramNames' => $paramNames,
            'pattern' => $pattern
        ];
    }
    
    private function buildPatternWithOptional($path, &$paramNames)
    {
        if ($path === '/') {
            return '#^/$#';
        }
        
        // Escape forward slashes
        $pattern = preg_quote($path, '#');
        
        // Replace {param} and {param?} with regex
        $pattern = preg_replace_callback(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)(\??)\\\}/',
            function($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                if ($matches[2] === '?') {
                    // Optional parameter
                    return '(?:(?P<' . $matches[1] . '>[^\/]+))?';
                }
                // Required parameter
                return '(?P<' . $matches[1] . '>[^\/]+)';
            },
            $pattern
        );
        
        // Adjust for optional segments
        $pattern = str_replace('\/)?', ')?', $pattern);
        
        return '#^' . $pattern . '$#';
    }
}