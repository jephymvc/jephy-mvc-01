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
        
        // Initialize view system (add this line)
        $this->initializeViewSystem();
        
        // Load helper (add this line)
        if (file_exists(__DIR__ . '/Helper.php')) {
            require_once __DIR__ . '/Helper.php';
            Helper::init();
        }
        
        $this->initialized = true;
    }
    
    /**
     * Initialize view system with global data
     */
    private function initializeViewSystem()
    {
        $config = $this->config;
        
        // Set global view variables if the view_global function exists
        if (function_exists('view_global')) {
            // Site information
            view_global('site', [
                'name' => $config->get('site.name', 'My Website'),
                'url' => $config->get('site.url', ''),
                'description' => $config->get('site.description', ''),
                'keywords' => $config->get('site.keywords', ''),
                'author' => $config->get('site.author', ''),
                'year' => date('Y'),
                'copyright' => '© ' . date('Y') . ' ' . $config->get('site.name', 'My Website')
            ]);
            
            // User information
            $guestKey = $config->get('session.guest', '');
            $adminKey = $config->get('session.admin', '');
            
            $guestIsLoggedIn = isset($_SESSION["user_session"][$guestKey]) && 
                              !empty($_SESSION["user_session"][$guestKey]);
            $adminIsLoggedIn = isset($_SESSION["user_session"][$adminKey]) && 
                              !empty($_SESSION["user_session"][$adminKey]);
            
            view_global('user', [
                'isLoggedIn' => $guestIsLoggedIn || $adminIsLoggedIn,
                'isGuest' => $guestIsLoggedIn,
                'isAdmin' => $adminIsLoggedIn,
                'name' => $_SESSION["user_session"]['name'] ?? null,
                'email' => $_SESSION["user_session"]['email'] ?? null,
                'id' => $_SESSION["user_session"]['id'] ?? null
            ]);
            
            // Application information
            view_global('app', [
                'debug' => $this->appDebugMode,
                'environment' => $config->get('site.environment', 'production'),
                'version' => $config->get('site.version', '1.0.0'),
                'path' => $this->appPath
            ]);
            
            // Session information
            view_global('session', $_SESSION ?? []);
            
            // Flash messages
            view_global('flash', $_SESSION['flash'] ?? []);
        }
        
        // Execute hook for additional global data
        if (isset($this->hooks)) {
            $this->hooks->exec('initGlobalViewData', [
                'framework' => $this,
                'config' => $config
            ]);
        }
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
		$this->smarty->caching = false;
		$this->smarty->debugging = false;
		
		// Force compile in development
		$this->smarty->force_compile = $this->appDebugMode;
		
		// For Smarty 4, use error_reporting property instead of muteExpectedErrors
		$this->smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED;
		
		// Alternatively, if you want to use muteExpectedErrors, check if method exists
		if (method_exists($this->smarty, 'muteExpectedErrors')) {
			$this->smarty->muteExpectedErrors();
		}
		
		$sessionKeys = explode("|", $this->config->get('session.keys', ''));
		$sessionKeys = array_map('trim', $sessionKeys);
		
		$guestIsLoggedIn = isset($_SESSION["user_session"][$this->config->get('session.guest', '')]) && 
						  ($_SESSION["user_session"][$this->config->get('session.guest', '')] != null || 
						   $_SESSION["user_session"][$this->config->get('session.guest', '')] != "") ? true:false;
		$adminIsLoggedIn = isset($_SESSION["user_session"][$this->config->get('session.admin', '')]) && 
						  ($_SESSION["user_session"][$this->config->get('session.admin', '')] != null || 
						   $_SESSION["user_session"][$this->config->get('session.admin', '')] != "") ? true:false;
		
		// Create compile directory if it doesn't exist
		$compileDir = $this->appPath . '/cache/views_c/';
		if (!is_dir($compileDir)) {
			mkdir($compileDir, 0755, true);
		}
		
		$this->smarty->assign("guestIsLoggedIn", $guestIsLoggedIn);
		$this->smarty->assign("adminIsLoggedIn", $adminIsLoggedIn);
		
		for ($i = 0; $i < count($sessionKeys); $i++) {
			$session = isset($_SESSION["user_session"][$sessionKeys[$i]]) && 
					  ($_SESSION["user_session"][$sessionKeys[$i]] != null || 
					   $_SESSION["user_session"][$sessionKeys[$i]] != "") ? true:false;
			$this->smarty->assign("session".ucfirst($sessionKeys[$i]), $session);
		}
	}


    private function registerSmartyPlugins()
    {
        $this->smarty->registerPlugin('function', 'hook', [$this, 'smartyHookFunction']);
        $this->smarty->registerPlugin('block', 'hookblock', [$this, 'smartyHookBlock']);
        
        // Register asset function
        $this->smarty->registerPlugin('function', 'asset', function($params) {
            return asset($params['path'] ?? '');
        });
        
        // Register url function
        $this->smarty->registerPlugin('function', 'url', function($params) {
            return url($params['path'] ?? '');
        });
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
            
            // Handle based on handler type
            switch ($routeInfo['handlerType']) {
                case 'closure':
                    // Execute closure with parameters
                    $result = call_user_func_array($routeInfo['handler'], $routeInfo['params']);
                    if (is_string($result)) {
                        echo $result;
                    }
                    break;
                    
                case 'callable_array':
                    // Execute [new Controller(), 'method']
                    $result = call_user_func_array([$routeInfo['controller'], $routeInfo['action']], $routeInfo['params']);
                    if (is_string($result)) {
                        echo $result;
                    }
                    break;
                    
                case 'static_callable':
                case 'static_method':
                    // Static method call Controller::method
                    $controllerClass = $routeInfo['controller'];
                    
                    // Add namespace if not already present
                    if (strpos($controllerClass, 'App\\Controllers\\') === false && strpos($controllerClass, '\\') === false) {
                        $controllerClass = "App\\Controllers\\" . $controllerClass;
                    }
                    
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller class not found: $controllerClass");
                    }
                    
                    // Check if method exists
                    if (!method_exists($controllerClass, $routeInfo['action'])) {
                        throw new Exception("Static method {$routeInfo['action']} not found in controller $controllerClass");
                    }
                    
                    // Execute static method
                    $result = call_user_func_array([$controllerClass, $routeInfo['action']], $routeInfo['params']);
                    if (is_string($result)) {
                        echo $result;
                    }
                    break;
                    
                case 'instance_method':
                    // Instance method call Controller@method
                    $controllerName = $routeInfo['controller'];
                    $controllerClass = "App\\Controllers\\" . $controllerName;
                    
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller not found: $controllerClass");
                    }
                    
                    $controller = new $controllerClass();
                    
                    if (!method_exists($controller, $routeInfo['action'])) {
                        throw new Exception("Method {$routeInfo['action']} not found in controller $controllerClass");
                    }
                    
                    // Get method parameters using reflection
                    $methodReflection = new \ReflectionMethod($controllerClass, $routeInfo['action']);
                    $parameters = $methodReflection->getParameters();
                    
                    // Prepare arguments based on method signature
                    $args = [];
                    foreach ($parameters as $param) {
                        $paramName = $param->getName();
                        
                        if (isset($routeInfo['params'][$paramName])) {
                            $args[] = $routeInfo['params'][$paramName];
                        } elseif ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } else {
                            $args[] = null;
                        }
                    }
                    
                    // Call controller method
                    $result = call_user_func_array([$controller, $routeInfo['action']], $args);
                    if (is_string($result)) {
                        echo $result;
                    }
                    break;
                    
                default:
                    throw new Exception("Unknown handler type: {$routeInfo['handlerType']}");
            }
            
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
    
    public function getConfig() {
        return $this->config;
    }
    
    public function getAppDebugMode() {
        return $this->appDebugMode;
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