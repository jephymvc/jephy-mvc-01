<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define( 'ROOT_PATH', dirname(__DIR__));
// Include Composer autoload if exists
$autoloadPath = str_replace( "\\", "/", ROOT_PATH ) . '/vendor/autoload.php';

if ( file_exists( $autoloadPath ) ) {
    require_once $autoloadPath;    
}

use App\Core\Framework;
$router = Framework::getRouter();

// ==================== DEFINE YOUR ROUTES HERE ====================

// 	Basic routes
$router->addRoute( 'GET', '/', 'HomeController@index' );
$router->addRoute( 'GET', '/home', 'HomeController@index' );
$router->addRoute( 'POST', '/contact', 'HomeController@handleContactMessage' );	
$router->addRoute( 'POST', '/subscribe-newsletter', 'HomeController@subscribeNewsletter' );	

// 	404 route should be last
$router->addRoute( 'GET', '/notfound', 'ErrorController@notFound' );
$router->addRoute( 'GET', '/{any}', 'ErrorController@notFound' );
$router->addRoute( 'GET', '/{any}/{any}', 'ErrorController@notFound' );
$router->addRoute( 'GET', '/{any}/{any}/{any}', 'ErrorController@notFound' );
$router->addRoute( 'GET', '/{any}/{any}/{any}/{any}', 'ErrorController@notFound' );

// ==================== START THE APPLICATION ====================
Framework::getInstance()->run();