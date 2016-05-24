<?php 

$config = array(	
	/** 
	 * Database Login
	 */
	
	"database"                           => array(
		"type"                       =>"mysql",
		"user"                       =>"root",
		"pass"                       =>"",
		"host"                       =>"",
		"database"                   =>"",
	),
	
	/** 
	 * Basic Page and web Caching Setting
	 */
	"cache"                            => array(
		"page"                       => true
	),
	
	"logs"                               => array(
		"access"                         => array(
			"backup"                 => false
		)
	),
);

/** 
 * initialize config
 */
Config::init($config);
unset($config);



/** 
 * Examples Docs
 */

/*
SQLITE PARAMS
	'type' => 'sqlite',
	'file' => '/Applications/MAMP/Projects/backend/crud/trunk/db/brandon.sqlite'
);
*/
?>