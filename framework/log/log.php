<?php

class Log
{
	public static $config = array();
	
	public static function config() 
	{ 
		self::$config = Config::$config['logs'];
	}
	
	/** 
	 * Write to Error Log
	 */
	public static function error() 
	{ 
		
	}
	
	/** 
	 * Write to Content Update Log
	 */
	public static function update($text) 
	{ 
		if (self::$config['update']['enabled']) {
			$log = self::$config['update']['file'];
			$dir = self::$config['dir'];
			
			$log_file = APP . "$dir/$log";

			if (is_file($log_file)) {
				//[09-Mar-2011 15:41:42]
				$date = '['. date('d-M-Y G:i:s') .'] ';
				$ip = $_SERVER['REMOTE_ADDR'] . ' ';
				$url = URL::$path . ' ';

				$message = $date . $ip . $url . $text . "\n";

				return file_put_contents( $log_file, $message, FILE_APPEND );	
			}
		}
	}
	
	
	/** 
	 * Write to the Access Log
	 */
	public static function access()
	{	
		//if logs > access is enabled in Config		
		if (Config::$config['logs']['access']['enabled']) {
			//write to access log
			$log = self::$config['access']['file'];
			$dir = self::$config['dir'];
			$log_file = APP . "$dir/$log";
			if (is_dir(APP.$dir)) {
				//-----------------------------------------------------------------------
				//write out log messges
				// ex. [09-Mar-2011 15:41:42]
				//-----------------------------------------------------------------------
				$date = "Date: " . date('d-M-Y G:i:s') . "\n";
				$ip = "IP: ". $_SERVER['REMOTE_ADDR'] . "\n";
				$url = 'URL: ' . URL::$protocol."://"  . URL::$domain . URL::$raw ."\n";
				$referer = "Referer: Direct Access \n";
				if (isset($_SERVER['HTTP_REFERER'])) {
					$referer = "Referer: " . $_SERVER['HTTP_REFERER'] . "\n";
				}
				$ua = "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
				//-----------------------------------------------------------------------
				
				$message = $date . $ip . $url . $referer . $ua . "\n";
				file_put_contents( $log_file, $message, FILE_APPEND );
				
				//check to see if file should be archived
				self::archiveAndClear('access');
			}
		}   
	}
	
	
	/** 
	 * This function takes an argument for an existing log to archive and clear
	 */
	public static function archive($log = "") 
	{ 
		$dir = self::$config['dir'];
		$file = APP . $dir . "$log.log";
		$archive = APP . "$dir/archive/$log/$log ". date('Y-m-d-G-i-s') .".log";
		
		if (!is_file($file)) {
			throw new Exception("$file does not exist");
		}
		
		if (!copy($file, $archive)) {
			throw new Exception("The Log file exists but could not be archived");
		}
		unlink($file);
		file_put_contents($file, "");
	}
	
	/** 
	 * Automatically archive and clear logs that get bigger then 256k
	 */
	private static function archiveAndClear($log = "") 
	{ 
		if (!isset(self::$config[$log])) {
			throw new Exception(self::$config['dir'] . "$log.log does not exist");
		}
		
		//get file creation date
		$path = APP . self::$config['dir'] . "$log.log";
		$file = new SplFileObject($path);
		$size = round($file->getSize() / 1024, 2);
		
		//if the file is bigger then 256k
		if ($size > 512) {
			self::archive($log);
		}		
	}
}


?>