<?php


/** 
 * Config Class
 * 
 * Required Dependencies URL & DB Class
 */
namespace Plash;

class Config
{	
	public static $config;
	protected static $default = array(
		/** 
		 * Routing
		 */
		"routing"                            => array(
			"custom_enabled"             => true,
			"default"                        => array(
		        "module"                 => "home",
		        "action"                 => "index"
			)
		),

		/** 
		 * Database Login
		 */
		"database"                           => array(
			"type"                       =>"mysql",
			"user"                       =>"root",
			"pass"                       =>"cH0p5y",
			"host"                       =>"localhost",
			"database"                   =>"test",
		),


		/** 
		 * Basic Page and web Caching Setting
		 */
		"cache"                               => array(
			"dir"                        => "tmp/cache/",
			"page"                       => true,
			"browser"                    => false
		),

		/** 
		 * Set Timezone
		 */
		"timezone"                       =>"America/Los_Angeles",

		/** 
		 * Configure Sessions
		 */
		"sessions"                           => array(
			"lifetime"                   => 7200,
			"dir"                       => "tmp/sessions/"
		),

		/** 
		 * Logging 
		 */
		"logs"                               => array(
			"dir"                        => "tmp/logs/",
			"access"                         => array(
		        "file"                   => "access.log",
				"enabled"                => true,
				"backup"                 => true
			),
			"error"                          => array(
				"email_admin"            => false,
		        "enabled"                => true,
		        "file"                   => "error.log",
				"backup"                 => false
			),
			"update"                         => array(
		        "file"                   => "update.log",
				"enabled"                => true,
				"backup"                 => false
			)
		),
		/** 
		 * Set View Modes
		 * View Modes will be removed from the path array
		 */
		"modes"                              => array(
			"admin_url"                  =>"admin",
		)
	);
	
	
	
	
	/** 
	 * Initialize Config
	 * @param Array
	 * @return void
	 * called from config/config.php
	 */

	public static function init($config = array()) 
	{ 
		self::$config = self::mergeConfig(self::$default, $config);
		
		//configure timezone and sessions
		self::timeZone();
		self::sessions();
	}
	
	
	/** 
	 * Dumps the config array to the browser window
	 */
	public static function dump() 
	{ 
		echo "\n<pre>\n", print_r(self::$config), "\n</pre>\n";
	}
	


	//------------------------------------------------------
	//               Internal Methods
	//------------------------------------------------------	
	
	/** 
	 * Merge the App and default config arrays together
	 */
	private static function mergeConfig($default, $app=array()) 
	{ 
		foreach($app as $key => $value)
		  {
		    if(array_key_exists($key, $app) && is_array($value))
		      $default[$key] = self::mergeConfig($default[$key], $app[$key]);

		    else
		      $default[$key] = $value;

		  }
		  return $default;
	}

	
	/** 
	 * Configure sessions
	 * @return void
	 * called from Config::init()
	 */
	private static function sessions()
	{
	    session_save_path(APP . rtrim(self::$config['sessions']['dir'], '/'));
		session_set_cookie_params(self::$config['sessions']['lifetime'], '/');
	}

	/** 
	 * set the timezone
	 * @return void
	 * called from Config::init()
	 */
	private static function timeZone() 
	{ 
		date_default_timezone_set(self::$config['timezone']);
	}
}



?>