<?php
//set the enviornment that the application is running in
//possible values are: dev, testing, prod
define("ENV", 'dev');

//define path to application folder
define("APP", "../application/");

//define path to the framework library folder
define("LIB", "../framework/");

//bootstrap dat ho
include APP ."init.php";

?>