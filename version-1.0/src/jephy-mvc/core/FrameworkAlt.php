<?php
namespace App\Core;

use Exception;
use App\Hooks\GlobalDataHook;
use App\Core\AppUIHelper;
use App\Core\AppUIBooleanHelper;
//	if( !defined( 'APP_PATH' ) ) define( 'APP_PATH', "" );

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
    
	  
    private function __construct()
    {        
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
        
		// Initialize Smarty first (no dependencies)
        $this->initializeSmarty();
        
        // Initialize Hook Manager (no dependencies)
        $this->hooks 	= new HookManager();
        
        // Initialize Router (needs hooks)
        $this->router 	= new Router($this->hooks);
        
        // Initialize Database (needs config constants)
        $this->db 		= Database::getInstance();
        
        // Initialize Template Hook (needs hooks)
        $this->templateHook = new TemplateHook($this->hooks);
        
        // Register Smarty plugins
        $this->registerSmartyPlugins();
        
        // Load core hooks
        $this->loadCoreHooks();
		
    }
    
    private function initializeSmarty()
    {
        
		$this->smarty 	= new \Smarty();
		$APP_PATH 		= Config::getInstance()->get( 'site.app_path' );
		
		// Debug: Check what value we're getting
		error_log("APP_PATH from config: " . $APP_PATH);
		error_log("Config file exists: " . (file_exists(dirname(__DIR__) . '/config.conf') ? 'Yes' : 'No'));
		
		// 	If empty, set a default
		//	if (empty($APP_PATH)) {
		//		$APP_PATH = dirname(__DIR__, 2); // Go up 2 levels from mvc/core/
		//		error_log("Using default APP_PATH: " . $APP_PATH);
		//	}
		
		
		$templateDir = $APP_PATH . '/views/';
		$compileDir = $APP_PATH . '/cache/views_c/';
		
		error_log("Template dir: " . $templateDir . " - Exists: " . (is_dir($templateDir) ? 'Yes' : 'No'));
		error_log("Compile dir: " . $compileDir . " - Exists: " . (is_dir($compileDir) ? 'No - will create' : 'No'));
		
		// Create compile directory if it doesn't exist
		if (!is_dir($compileDir)) {
			mkdir($compileDir, 0755, true);
		}
		
		exit;	
		
        // Configure Smarty paths
        $this->smarty->setTemplateDir($templateDir);
		$this->smarty->setCompileDir($compileDir);
        $this->smarty->setCacheDir( $APP_PATH . '/cache/' );
        $this->smarty->setConfigDir( $APP_PATH . '/config/' );
        
        // Smarty configuration
        $this->smarty->caching     = false;
        $this->smarty->debugging   = false;		
        
        // Force compile in development
        if ( Config::getInstance()->get( 'site.app_debug' ) == 'true' ) {
			$this->smarty->force_compile = true;
        }
		
		
		$userAuthnHelper 		= new AppUIBooleanHelper();		
		$this->smarty->registerObject( 'UIHelper', $userAuthnHelper );		
		
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
    
    private function loadCoreHooks()
    {
        // Load hook files from app/hooks directory
		$hookPath = $this->appPath . '/hooks/';
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
    
    public function run()
    {
        try {
            $uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $method = $_SERVER['REQUEST_METHOD'];            
            $route   = $this->router->route($uri, $method);
            
            list($controllerShortName, $actionName) = explode('@', $route['controllerAction']);
            
            // Convert short name to full controller name
            // If route is "Home@index", controller file is "HomeController.php"
            // and class is "App\Controllers\HomeController"
            $controllerName = $controllerShortName;
            $controllerFile = Config::getInstance()->get( 'site.app_path' ) . "/controllers/{$controllerName}.php";			
			
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controllerFile}");
            }
            
            require_once $controllerFile;
            
            // Build the full class name with namespace
            $controllerClass = "App\\Controllers\\" . $controllerName;
            
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class not found: {$controllerClass}. Make sure the class has namespace: App\\Controllers");
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
        
		if ( Config::getInstance()->get( 'site.app_debug' ) == 'true' ) {
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
            // Load Mailer class if needed
            if (!class_exists('App\\Core\\Mailer')) {
                require_once __DIR__ . '/Mailer.php';
            }
            $this->mailer = new Mailer();
        }
        return $this->mailer;
    }
    
    // Getters
    public static function getSmarty() { return self::getInstance()->smarty; }
    public static function getHooks() { return self::getInstance()->hooks; }
    public static function getRouter() { return self::getInstance()->router; }
    public static function getDatabase() { return self::getInstance()->db; }
    public static function getTemplateHook() { return self::getInstance()->templateHook; }
    public static function getMailer() { return self::getInstance()->getMailerInstance(); }
    
    public static function getGlobalDataHook()
    {
        return GlobalDataHook::getInstance();
    }
	
	
	
}

