<?php
namespace Plash;

class Autoload
{
	//paths to libraries
	private static $paths;
	
	public static function addPath($path = '') 
	{ 
		if (is_dir($path)) {
		    self::$paths[] = $path;
		}
	}
	
	//load util files
	public static function lib($class) 
	{ 		
		$class = strtolower($class);
		foreach (self::$paths as $path) {
			$file = "$path/$class/$class.php";
		    if (is_file($file)) {
		        require_once $file;
		    }
		}
	}
	
	//load modules
	public static function modules($class) 
	{ 		
		$class = strtolower($class);		
		foreach (self::$paths as $path) {
			$file = $path."$class/controller.php";
		    if (is_file($file)) {
		        require_once $file;
		    }
		}
	}
	
	//make another autoload funcion here and register it below or in init
	public static function dump() 
	{ 
		echo "\n<pre>\n", print_r(self::$paths), "\n</pre>\n";	
	}
}
spl_autoload_register(array('AutoLoad', 'lib'), false);
spl_autoload_register(array('AutoLoad', 'modules'), false);
spl_autoload_register(array('AutoLoad', 'extensions'), false);
spl_autoload_register(array('AutoLoad', 'plugins'), false);


AutoLoad::addPath(LIB);
AutoLoad::addPath(APP.'modules/');
AutoLoad::addPath(APP.'extensions/');
AutoLoad::addPath(APP.'plugins/');

?>