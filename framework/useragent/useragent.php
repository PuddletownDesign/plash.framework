<?php

class UserAgent
{
	public static $raw, $device, $device_version, $browser, $browser_version;
	
	public static function config()
	{
	    self::$raw = $_SERVER['HTTP_USER_AGENT'];
	}
	

}


?>