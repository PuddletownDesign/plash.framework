<?php

/** 
 * Cache Class
 * Requires URL class
 */

/** 
 * Dependencies
 *  - Url class
 * 
 */

/** 
 * Cache API

 *  global configure for cache
    Cache::Config(array('enabled' => true));
	
 *	Enable or disable caching at any point in the script	
	Cache::enable(true)
  
 *  checks for the the current url against the cache
	Cache::check()

*   deletes the cache (takes a url as a param)
	Cache::delete('blog/')
	
 */
namespace Plash;

class Cache
{
	//------------------------------------------------------
	//               Internal Properties
	//------------------------------------------------------
	
	public static $config;
	
	//config
	public static $dir;
	
	public static $enabled = true, $enable_page_caching = true, $enable_browser_caching = false;
	
	//page cache
	private static $abso, $rel, $filetype = '.html', $file_name;
	
	//file info
	private static $last_modified, $etag, $content_length;
	
	//browser_cache
	private static $browser_last_modified, $browser_etag, $browser_content_length;
	
	
	//------------------------------------------------------
	//               Public Methods
	//------------------------------------------------------
	
	/** 
	 * Called From lib/config.php
	 */
	public static function config() 
	{ 
		self::$config = Config::$config['cache'];
		self::$dir = self::$config['dir'];
		//set the cache urls
		self::setCacheURLs();
	}
	
	/** 
	 * Toggle Caching On and Off
	 */	
	public static function enable($enabled = true) 
	{ 
		self::$enabled = $enabled;
	}
	
	//checks the cache for a url match
	//loads the cached page and exits app if successful
	public static function check() 
	{ 
		//check if page caching is enabled
		if (Config::$config['cache']['page'] == true) {			
			if (file_exists(self::$abso)) {
				//check if browser caching is enabled
				//if (self::$config['browser'] == true) {
				//	//run browser caching
				//	self::getBrowserCache();
				//}				
				//get page cache contents
				$file = file_get_contents(self::$abso);
				
				//display the file contents and then exit the app
				print $file;
				exit;
			}
		}
	}
	
	
	//start the caching buffer
	public static function start($file_name="") 
	{ 
		self::$file_name = $file_name;
		if (self::$enabled == true) {
			ob_start();
		}
	}
	
	//ends caching buffer
	public static function stop() 
	{ 
		if (self::$enabled == true) {
			self::create();
			ob_end_flush();
		}
	}
	
	//clears specific pages or directories from the cache 1
	public static function delete($path="") 
	{ 		
		$path_array = explode("/", $path);
		$c = count($path_array)-1;
		
		//check if path is a directory
		
		if ($path_array[$c] == "") {
			self::removeDirectory($path);
		}
		
		//path is a file
		else {
			self::removeFile($path);
		}
	}
	
	//------------------------------------------------------
	//               Internal Methods
	//------------------------------------------------------
	
	
	//------------------------------------------------------
	//               Page Cache
	//------------------------------------------------------
	
	/** 
	 * creates a page cache
	 */
	private static function create() 
	{ 
		if (self::$enabled == true) {
			self::makeCacheDirectory();
			file_put_contents(self::$abso, ob_get_contents());
		} 
	}
	
	/** 
	 * removes a cache file
	 */
	private static function removeFile($path) 
	{ 
		$file = APP . self::$dir . $path . self::$filetype;
		if (is_file($file)) {
			unlink($file);
		}
	}
	
	/** 
	 * Recursively remove a cache directory
	 */
	private static function removeDirectory($path) 
	{ 
		$dir = APP . self::$dir . $path;
		if (is_dir($dir)) {
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
			                RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach ($iterator as $file) {
				if ($file->isDir()) {
					rmdir($file->__toString());
				} else {
					unlink($file->__toString());
				}
			}
			
			//delete directory as long as it's not the root cache directory
			if ($dir != APP . self::$dir) {
				rmdir($dir);
			}
		}
	}
	
	/** 
	 * set the abso and rel properties
	 */
	private static function setCacheURLs() 
	{ 
		//count directory array and subtract 1 from count
		$c = count(URL::$dirs)-1;
		
		//if the last one is blank then we know it's a directory index page
		$current_page = "";
		if (URL::$dirs[$c]=="") {
			$current_page = "index";
		}
		
		self::$rel = URL::$path . $current_page .self::$filetype;
		
		self::$abso = APP . self::$dir . self::$rel;

	}
	
	
	/** 
	 * get the cache directory for the file
	 */
	private static function getCacheDirectory() 
	{ 
		$cache_dir = explode("/", self::$abso);
		$c = count($cache_dir)-1;
		unset($cache_dir[$c]);
		return implode("/", $cache_dir)."/";
	}
	
	/** 
	 * check to see if the cache directory exists
	 */
	private static function makeCacheDirectory() 
	{ 	
		
		$dir_array = explode("/", self::$rel);
		
		$c = count($dir_array);
		$dir = APP . self::$dir;
		
		for($i=0; $i < $c; $i++) { 
			$dir .= $dir_array[$i]."/";
			
			$j = $i+1;
			if (isset($dir_array[$j])) {
				if (!is_dir($dir)) {
		   			mkdir($dir);
		   		}
		   }
		}
	}
	
	
	//------------------------------------------------------
	//               Browser Cache
	//------------------------------------------------------
	
	/** 
	 * gets the browser cache values and the file cache values
	 */
	private static function getBrowserCache() 
	{ 		
		self::getFileInfo();		
		
		//check to see if the modified since value is there		
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
		//or isset($_SERVER['HTTP_IF_NONE_MATCH'])
		) {
			//if the browser has not been updated since the last page update
			if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) <= strtotime(self::$last_modified)
			//or $_SERVER['HTTP_IF_NONE_MATCH'] == self::$etag
			) {
				header('HTTP/1.0 304 Not Modified');
				exit;
			}
		}
		self::sendHeaders();
	}
	
	/** 
	 * get info on the file
	 */
	private static function getFileInfo() 
	{ 
		$file = new SplFileObject(self::$abso);
		//get last modified
		self::$last_modified = gmdate('D, d M Y H:i:s', $file->getMTime());
		//get file length
		self::$content_length = $file->getSize();
		//get eTag
		self::$etag = md5(self::$abso . self::$last_modified);
	}
	
	/** 
	 * send Cache Headers
	 */
	private static function sendHeaders() 
	{ 
		header('Last-Modified: ' . self::$last_modified . ' GMT');
		header("Content-Length: ".self::$content_length);
		header('Etag: '.self::$etag);
	}
}


?>