<?php
use App\Core\Router;

Router::get( '/', 'HomeController@index');
//	Router::get( '/home', 'HomeController@index');
Router::get( '/documentation', 'HomeController@documentation');
Router::get( '/documentation/{target}', 'HomeController@documentation');
Router::get( '/home', function(){
	return view( 'home/index.tpl' );
});
Router::post( '/contact', 'HomeController@handleContactMessage' );



?>