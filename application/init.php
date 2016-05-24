<?php
//Set Error Reporting
error_reporting(E_ALL - E_STRICT);

//Autoload Everything
include LIB.'/autoload.php';

//Initialize application configuration
include 'config-dev.php';

//set up the url in routing
URL::config();

//load authentication
$user = Auth::getInstance();

//get user agent data
//UserAgent::config();

//check cache
Cache::enable(false);
Cache::config();
if (isset($user->user['user'])) {
	Cache::enable(false);
} else {
	Cache::check();
}
unset($user);




//route url to module
Routing::match();


?>