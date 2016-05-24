<?php 

$config = array(	
	/** 
	 * Database Login
	 */
	
	"database"                   => array(
		"type"                       =>"mysql",
		"user"                       =>"",
		"pass"                       =>"",
		"host"                       =>"",
		"database"                   =>"",
	),

	
	/** 
	 * Set Error File Locations
	 */
	//"error"                             => array(
	//	"404"                        => 'modules/global/views/404.php'
	//),
	
	/** 
	 * Basic Page and web Caching Setting
	 */
	"cache"                            => array(
		"page"                       => false
	),
	
	"logs"                               => array(
		"access"                         => array(
			"backup"                 => false
		)
	),

	/** 
	 * Set View Modes
	 * View Modes will be removed from the path array
	 * define mode label and mode url
	 */
	"modes"                              => array()
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