<?php

class Files
{
	/** 
	 * checks if a directory exists
	 * @param string (url)
	 * @return BOOLEAN
	 */
	private static $hidden_files = array('.DS_Store');
	
	public static function directoryExists($dir) 
	{ 
		if (is_dir($dir)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/** 
	 * creates a directory
	 * @param string (url)
	 * @return NULL
	 */
	public static function createDirectory($dir) 
	{ 
		if (!self::directoryExists($dir)) {
			mkdir($dir, 0777);
		}
	}
	
	public static function getSubDirectories($dir)
	{
	    if (is_dir($dir)) {
	    	$di = new DirectoryIterator($dir);
			$sub_dirs = array();
			while ($di->valid()) {
				if (!$di->isDot()) {
					$path = $di->__toString();
					if ($di->isDir() && !in_array($path, self::$hidden_files)) {
						$sub_dirs[] = strtolower($path);
					}
				}
				$di->next();
			}
			return $sub_dirs;
	    }
		return false;
	}
	
	public static function getFiles($dir) 
	{ 
		if (is_dir($dir)) {
			$di = new DirectoryIterator($dir);
			$files = array();
			while ($di->valid()) {
				if (!$di->isDot()) {
					$path = $di->__toString();
					if (!$di->isDir() && !in_array($path, self::$hidden_files)) {
						$files[] = strtolower($path);
					}
				}
				$di->next();
			}
			return $files;
		}
		return false;
	}
	
	public static function getDirectoryContents($dir) 
	{ 
		if (is_dir($dir)) {
			$di = new DirectoryIterator($dir);

			while ($di->valid()) {
				if (!$di->isDot() && !in_array($path, self::$hidden_files)) {
					$path = $di->__toString();
					$contents[] = strtolower($path);
				}
				$di->next();
			}
			return $contents;
		}
		return false;
	}
}


?>