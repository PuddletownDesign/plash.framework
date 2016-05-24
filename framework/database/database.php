<?php
namespace Plash;

class Database
{
	static $_instance, $type;
	private static $db, $dsn;
	private function __construct() {}
	
	public function __clone() {}
	
	/** 
	 * -------------------------------------- 
	 * Get instance of Pdo
	 * -------------------------------------- 
	 * @return object
	 * singleton wrapper for pdo
	 */
	public static function getInstance() 
	{ 
		if (!self::$_instance) {	
			self::$db = Config::$config['database'];
			
			unset(Config::$config['database']);
					
			try 
			{
				self::$_instance = self::connect();	
			}
			catch(Exception $e) { 
				echo $e->__toString();
			}
			self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return self::$_instance;
	}
	
	/** 
	 * -------------------------------------- 
	 * Database Connect
	 * -------------------------------------- 
	 * @return object
	 * 
	 * Connection Method
     * add support for database type and drivers here
     * when adding a new db type or driver create a DSNstring creation function
     * and add a new case to this switch statement
	 */

	private static function connect() 
	{ 
		if (!isset(self::$db['type'])) {
			throw new Exception("Set a db type");
		}
		else {
			self::$type = self::$db['type'];
		}
		switch (self::$db['type']) {
			case 'mysql':	
				self::PDOmysqlDSN();
				return new PDO(self::$db['dsn'], self::$db['user'], self::$db['pass']);
			break;
			
			case 'sqlite':
				self::PDOsqliteDSN();
				return new PDO(self::$db['dsn']);
			break;	
			default:
				throw new Exception("Please set a db type supported types and drivers are pdo sqlite and pdo mysql");			
		}
	}
	
	/** 
	 * -------------------------------------- 
	 * Build Database DSN's
	 * -------------------------------------- 
	 * @return null
	 * Builds the DNS for each supported Database Type
	 */
	private static function PDOmysqlDSN() 
	{ 
		if (isset(self::$db['user']) && isset(self::$db['pass'])) {
			self::$db['dsn'] = self::$db['type'].':host='.self::$db['host'].';dbname='.self::$db['database'];	
		} else {
			throw new Exception("Set a user and pass in the db array");
		}
	}
	private static function PDOsqliteDSN() 
	{ 
		if (isset(self::$db['file'])) {
			self::$db['dsn'] = 'sqlite:/'.self::$db['file'];
		}
		else {
			throw new Exception("Set a sqlite 'file' in db login. set it to absolute file location");
		}
	}
	
	//------------------------------------------------------
	//               Helper Methods
	//------------------------------------------------------
	
	/** 
	 * -------------------------------------- 
	 * Sets the DB Login Array
	 * -------------------------------------- 
	 * @return null
	 * called from config/config.php
	 */
	public static function config(Array $config)
	{
		self::$db = $config;
	}
	
    /**
    * -------------------------------------- 
    * Closes the DB Connection
    * -------------------------------------- 
    * @return null
    * called from init.php
    */
	public static function closeConnection() 
	{ 
		self::$_instance = null;
	}
	
	
}
?>