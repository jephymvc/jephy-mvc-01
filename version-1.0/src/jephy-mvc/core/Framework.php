<?php
namespace App\Core;

use Exception;
use App\Hooks\GlobalDataHook;
use App\Core\AppUIHelper;
use App\Core\AppUIBooleanHelper;
use App\Core\Config;
use App\Core\HookManager;
use App\Core\Router;
use App\Core\Database;
use App\Core\TemplateHook;
use App\Core\Mailer;

class Framework
{
    private static $instance;
    private $smarty;
    private $hooks;
    private $router;
    private $db;
    private $mailer;
    private $templateHook;
    private $config;
    private $appPath;
    private $appDebugMode;
    private $initialized = false;
    
    private function __construct()
    {        
        // Initialize Config first
        $this->config = Config::getInstance();
        
        // Get app path from config
        $this->appPath = $this->config->get('site.app_path', '');
        if (empty($this->appPath)) {
            // Calculate from current file location (mvc/core/)
            $this->appPath = dirname(__DIR__, 2);
        }
        
        // Get debug mode
        $this->appDebugMode = filter_var(
            $this->config->get('site.app_debug', 'false'), 
            FILTER_VALIDATE_BOOLEAN
        );
        
        // Set error reporting
        if ($this->appDebugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        $this->initialize();		
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initialize()
    {
        if ($this->initialized) {
            return;
        }
        
        // Initialize Smarty first (no dependencies)
        $this->initializeSmarty();
        
        // Initialize Hook Manager
        $this->hooks = new HookManager();
        
        // Initialize Router (needs hooks)
        $this->router = new Router($this->hooks);
        
        // Initialize Database
        $this->db = Database::getInstance();
        
        // Initialize Template Hook (needs hooks)
        $this->templateHook = new TemplateHook($this->hooks);
        
        // Register Smarty plugins
        $this->registerSmartyPlugins();
        
        // Load core hooks
        $this->loadCoreHooks();
        
        $this->initialized = true;
		
    }
    
    private function initializeSmarty()
    {
        if ($this->smarty !== null) {
            return;
        }
        
        $this->smarty = new \Smarty();
        
        // Configure Smarty paths
        $this->smarty->setTemplateDir($this->appPath . '/views/');
        $this->smarty->setCompileDir($this->appPath . '/cache/views_c/');
        $this->smarty->setCacheDir($this->appPath . '/cache/');
        $this->smarty->setConfigDir($this->appPath . '/config/');
        
        // Smarty configuration
        $this->smarty->caching 		= false;
        $this->smarty->debugging 	= false;
        
        // Force compile in development
        $this->smarty->force_compile = $this->appDebugMode;
		
		$sessionKeys 	= explode( "|", $this->config->get('session.keys', '' ) );
		$sessionKeys    = array_map( function( $key ){
			return trim($key);
		}, $sessionKeys );
		
		$guestIsLoggedIn = isset( $_SESSION[ "user_session" ][ $this->config->get('session.guest', '') ] ) && ( $_SESSION[ "user_session" ][ $this->config->get('session.guest', '') ] != null || $_SESSION[ "user_session" ][ $this->config->get('session.guest', '') ] != "" ) ? true:false;
		$adminIsLoggedIn = isset( $_SESSION[ "user_session" ][ $this->config->get('session.admin', '') ] ) && ( $_SESSION[ "user_session" ][ $this->config->get('session.admin', '') ] != null || $_SESSION[ "user_session" ][ $this->config->get('session.admin', '') ] != "" ) ? true:false;
        
        // Create compile directory if it doesn't exist
        $compileDir = $this->appPath . '/cache/views_c/';
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0755, true);
        }
		
		$this->smarty->assign( "guestIsLoggedIn", $guestIsLoggedIn  );
		$this->smarty->assign( "adminIsLoggedIn", $adminIsLoggedIn  );
		
		for( $i = 0; $i < count( $sessionKeys ); $i++ ){
			$session = isset( $_SESSION[ "user_session" ][ $sessionKeys[$i] ] ) && ( $_SESSION[ "user_session" ][ $sessionKeys[$i] ] != null || $_SESSION[ "user_session" ][ $sessionKeys[$i] ] != "" ) ? true:false;
			$this->smarty->assign( "session".ucfirst( $sessionKeys[$i] ), $session );
		}
		
        
        
		
    }
    
    private function registerSmartyPlugins()
    {
        $this->smarty->registerPlugin('function', 'hook', [$this, 'smartyHookFunction']);
        $this->smarty->registerPlugin('block', 'hookblock', [$this, 'smartyHookBlock']);
    }
    
    public function smartyHookFunction($params, $smarty)
    {
        if (!isset($params['name'])) {
            return '';
        }
        
        $hookName = $params['name'];
        $hookParams = $params['params'] ?? [];
        
        return $this->templateHook->display($hookName, $hookParams);
    }
    
    public function smartyHookBlock($params, $content, $smarty, &$repeat)
    {
        if (!$repeat) {
            $hookName = $params['name'] ?? '';
            $hookParams = $params['params'] ?? [];
            $hookParams['content'] = $content;
            
            $results = $this->hooks->execWithReturn($hookName, $hookParams);
            return implode('', $results);
        }
    }
    
    private function loadCoreHooksAlt()
    {
        // Load hook files from app/hooks directory
        $hookPath = $this->appPath . '/app/hooks/';
        if (is_dir($hookPath)) {
            $hookFiles = glob($hookPath . '*.php');            
            foreach ($hookFiles as $hookFile) {
                require_once $hookFile;                
                $className = pathinfo($hookFile, PATHINFO_FILENAME);
                $fullClassName = "App\\Hooks\\" . $className;
                if (class_exists($fullClassName)) {
                    $hookInstance = new $fullClassName();
                    if (method_exists($hookInstance, 'registerHooks')) {
                        $hookInstance->registerHooks($this->hooks);
                    }
                }
            }
        }
    }
	
	private function loadCoreHooksAlt2()
	{
		// Load hook files from app/hooks directory
		$hookPath = $this->appPath . '/app/hooks/';
		
		if (!is_dir($hookPath)) {
			// Try alternative path
			$hookPath = $this->appPath . '/hooks/';
		}
		
		if (is_dir($hookPath)) {
			$hookFiles = glob($hookPath . '*.php');
			
			foreach ($hookFiles as $hookFile) {
				try {
					require_once $hookFile;
					
					$className = pathinfo($hookFile, PATHINFO_FILENAME);
					$fullClassName = "App\\Hooks\\" . $className;
					
					if (class_exists($fullClassName)) {
						// Check if it's GlobalDataHook (singleton)
						if ($fullClassName === 'App\\Hooks\\GlobalDataHook') {
							$hookInstance = GlobalDataHook::getInstance();
						} else {
							$hookInstance = new $fullClassName();
						}
						
						if (method_exists($hookInstance, 'registerHooks')) {
							$hookInstance->registerHooks($this->hooks);
						}
					}
				} catch (\Exception $e) {
					error_log("Failed to load hook file {$hookFile}: " . $e->getMessage());
				}
			}
		}
		
		// Initialize GlobalDataHook if not already done
		$this->initializeGlobalDataHook();
	}
	
	private function loadCoreHooks()
	{
		// Load hook files from app/hooks directory
		$hookPath = $this->appPath . '/app/hooks/';
		
		if (!is_dir($hookPath)) {
			// Try alternative path
			$hookPath = $this->appPath . '/hooks/';
		}
		
		if (is_dir($hookPath)) {
			$hookFiles = glob($hookPath . '*.php');
			
			foreach ($hookFiles as $hookFile) {
				try {
					require_once $hookFile;
					
					$className = pathinfo($hookFile, PATHINFO_FILENAME);
					$fullClassName = "App\\Hooks\\" . $className;
					
					if (class_exists($fullClassName)) {
						// Check if it's GlobalDataHook (singleton)
						if ($fullClassName === 'App\\Hooks\\GlobalDataHook') {
							$hookInstance = GlobalDataHook::getInstance();
							// Initialize it
							if (method_exists($hookInstance, 'initialize')) {
								$hookInstance->initialize();
							}
						} else {
							$hookInstance = new $fullClassName();
						}
						
						if (method_exists($hookInstance, 'registerHooks')) {
							$hookInstance->registerHooks($this->hooks);
						}
					}
				} catch (\Exception $e) {
					error_log("Failed to load hook file {$hookFile}: " . $e->getMessage());
				}
			}
		}
		
		// Initialize GlobalDataHook if not already done
		$this->initializeGlobalDataHook();
	}

	private function initializeGlobalDataHook()
	{
		try {
			// Ensure GlobalDataHook is instantiated and initialized
			$globalDataHook = GlobalDataHook::getInstance();
			
			// Call initialize if needed
			if (method_exists($globalDataHook, 'initializeGlobalData')) {
				$globalDataHook->initializeGlobalData();
			}
			
			// Also register it with hooks if not already
			if (method_exists($globalDataHook, 'registerHooks')) {
				$globalDataHook->registerHooks($this->hooks);
			}
			
		} catch (\Exception $e) {
			error_log("Failed to initialize GlobalDataHook: " . $e->getMessage());
		}
	}
	
	public function run()
	{
		try {
			// Get current URI and method
			$uri = $_SERVER['REQUEST_URI'] ?? '/';
			$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
			
			// Route the request
			$routeInfo = $this->router->route($uri, $method);
			
			// Extract controller and action
			list($controllerName, $action) = explode('@', $routeInfo['controllerAction']);
			
			
			// Create controller instance
			$controllerClass = "App\\Controllers\\" . $controllerName;
			
			if (!class_exists($controllerClass)) {
				throw new Exception("Controller not found: $controllerClass");
			}
			
			$controller = new $controllerClass();
			
			// Check if method exists
			if (!method_exists($controller, $action)) {
				throw new Exception("Method $action not found in controller $controllerClass");
			}
			
			// Get method parameters using reflection
			$methodReflection = new \ReflectionMethod($controllerClass, $action);
			$parameters = $methodReflection->getParameters();
			
			// Prepare arguments based on method signature
			$args = [];
			
			foreach ($parameters as $param) {
				$paramName = $param->getName();
				
				// Check if parameter expects query array
				if ($paramName === 'query') {
					// Pass the query params array
					$args[] = $routeInfo['query'];
				}
				// Check if parameter matches a route parameter
				elseif (isset($routeInfo['params'][$paramName])) {
					$args[] = $routeInfo['params'][$paramName];
				}
				// Check if parameter exists in query string
				elseif (isset($routeInfo['query'][$paramName])) {
					$args[] = $routeInfo['query'][$paramName];
				}
				// Check for default value
				elseif ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					// Parameter not provided
					$args[] = null;
				}
			}
			
			// Call controller method with prepared arguments
			call_user_func_array([$controller, $action], $args);
			
		} catch (Exception $e) {
			$this->handleError($e);
		}
	}
	
		
	public function runAlt()
	{
		try {
			$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$method = $_SERVER['REQUEST_METHOD'];            
			$route = $this->router->route($uri, $method);
			
			list($controllerShortName, $actionName) = explode('@', $route['controllerAction']);
			
			// Convert short name to full controller name
			$controllerName = $controllerShortName;
			$controllerFile = $this->appPath . "/controllers/{$controllerName}.php";			
			
			if (!file_exists($controllerFile)) {
				throw new Exception("Controller file not found: {$controllerFile}");
			}
			
			require_once $controllerFile;
			
			// Build the full class name with namespace
			$controllerClass = "App\\Controllers\\" . $controllerName;
			
			if (!class_exists($controllerClass)) {
				throw new Exception("Controller class not found: {$controllerClass}");
			}
			
			$controller = new $controllerClass();
			
			if (!method_exists($controller, $actionName)) {
				throw new Exception("Action '{$actionName}' not found in controller '{$controllerClass}'");
			}
			
			// Execute before controller hook - pass as single array
			$hookParams = $this->hooks->exec('beforeController', [
				'controller' => $controllerClass,
				'action' => $actionName,
				'params' => $route['params']
			]);
			
			// Extract parameters from hook result
			$controllerName = $hookParams['controller'] ?? $controllerClass;
			$actionName = $hookParams['action'] ?? $actionName;
			$routeParams = $hookParams['params'] ?? $route['params'];
			
			// Execute action with parameters
			call_user_func_array([$controller, $actionName], $routeParams);
			
		} catch (Exception $e) {
			$this->handleError($e);
		}
	}
    
    
    public function runAlt1()
    {
        try {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $method = $_SERVER['REQUEST_METHOD'];            
            $route = $this->router->route($uri, $method);
            
            list($controllerShortName, $actionName) = explode('@', $route['controllerAction']);
            
            // Convert short name to full controller name
            #	$controllerName = $controllerShortName . 'Controller';
            $controllerName = $controllerShortName;
            $controllerFile = $this->appPath . "/controllers/{$controllerName}.php";			
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controllerFile}");
            }
            
            require_once $controllerFile;
            
            // Build the full class name with namespace
            $controllerClass = "App\\Controllers\\" . $controllerName;
            
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass();
            
            if (!method_exists($controller, $actionName)) {
                throw new Exception("Action '{$actionName}' not found in controller '{$controllerClass}'");
            }
            
            // Execute before controller hook
            $this->hooks->exec('beforeController', [
                'controller' => $controllerClass,
                'action' => $actionName,
                'params' => $route['params']
            ]);
            
            // Execute action with parameters
            call_user_func_array([$controller, $actionName], $route['params']);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function handleError(Exception $e)
    {
        // Execute error hook
        $this->hooks->exec('onError', [
            'exception' => $e,
            'uri' => $_SERVER['REQUEST_URI'] ?? ''
        ]);
        
        http_response_code(500);
        
        if ($this->appDebugMode) {
            echo "<h1>Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            echo "An error occurred. Please try again later.";
        }
        
        // Log error
        error_log("Framework Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    }
    
    // Instance method for Mailer
    private function getMailerInstance()
    {
        if ($this->mailer === null) {
            $this->mailer = new Mailer();
        }
        return $this->mailer;
    }
    
    // Public getters
    public function getSmartyInstance() { 
        return $this->smarty; 
    }
    
    public function getHooksInstance() { 
        return $this->hooks; 
    }
    
    public function getRouterInstance() { 
        return $this->router; 
    }
    
    public function getDatabaseInstance() { 
        return $this->db; 
    }
    
    public function getTemplateHookInstance() { 
        return $this->templateHook; 
    }
    
    public function getMailerInstancePublic() { 
        return $this->getMailerInstance(); 
    }
    
    // Static getters
    public static function getSmarty() { 
        return self::getInstance()->getSmartyInstance(); 
    }
    
    public static function getHooks() { 
        return self::getInstance()->getHooksInstance(); 
    }
    
    public static function getRouter() { 
        return self::getInstance()->getRouterInstance(); 
    }
    
    public static function getDatabase() { 
        return self::getInstance()->getDatabaseInstance(); 
    }
    
    public static function getTemplateHook() { 
        return self::getInstance()->getTemplateHookInstance(); 
    }
    
    public static function getMailer() { 
        return self::getInstance()->getMailerInstancePublic(); 
    }
    
    public static function getGlobalDataHook()
    {
        return GlobalDataHook::getInstance();
    }
    
    // Get app path
    public function getAppPath()
    {
        return $this->appPath;
    }
    
    public static function getAppPathStatic()
    {
        return self::getInstance()->getAppPath();
    }
    
    // Debug method
    public static function debug()
    {
        $instance = self::getInstance();
        return [
            'initialized' => $instance->initialized,
            'smarty' => $instance->smarty ? get_class($instance->smarty) : null,
            'appPath' => $instance->appPath,
            'debugMode' => $instance->appDebugMode
        ];
    }
}

