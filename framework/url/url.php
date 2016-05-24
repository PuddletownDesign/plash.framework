<?php

/** 
 * Url Config Class
 */

class URL
{
	public static $path, $domain, $abso, $cd, $rel, $protocol, $raw;
	public static $params = array(), $dirs = array();
		
	public static function config() 
	{ 	
		self::setProtocol();	
		self::setPath();
		self::setDomain();
		self::setAbso();
		self::setDirs();
		self::setCD();
		self::setRel();
		
	}
	
	/** 
	 * Get return the segment value of array
	 * returns segment number of array
	 * @param INT || null
	 * @return STRING || FALSE
	 * gets a segment from the url
	 * 
	 * ex. in http://cakes.com/chocolate/fudge
	 * URL::segment(1) returns "chocolate"
	 */
	
	
	public static function segment($i=null) 
	{ 
		//return the whole uri string
		if ($i==null or !is_int($i)) {
			return implode('/', self::$dirs);
		}
		//return just the requested segment
		else {
			//reduce int by one so that 0=1 in array
			$i = $i-1;
			//if the url segment number is not set return false
			if (isset(self::$dirs[$i])) {
				return self::$dirs[$i];
			}
			return false;
		}
	}
	
	/** 
	 * Redirect User to a new location by passing in directory path
	 */
	public static function redirect($path='/') 
	{ 
		$redirect = 'http://'. URL::$domain .$path;
		header("Location: $redirect");
		exit;
	}
	

	
	//check reffering domains
	//checks to see if the reffering domain is the local one
	//if not it exits the script or returns a 404
	public static function checkReferringDomain() 
	{ 
		$ref = $_SERVER['HTTP_REFERER'];
		$ref_arr = explode("/", $ref);
		$dom_arr = explode("/", self::$abso);
		if ($ref_arr[2] != $dom_arr[2]) {
			exit;
		}
	}
	
	/** 
	 * Checks to see if the first url segment is one of the set config modes
	 * if there is a match the mode is removed from the url string and set as a static 
	 * property of the routing class
	 * @param String
	 * @return STRING
	 */
	public static function getViewMode(Array $url) 
	{ 
		if (isset(Config::$config['modes'])) {
			$modes = Config::$config['modes'];
			
			if (in_array($url[0], $modes)) {
				//set Routes::$mode and slices it off the beginnning of the url array
				self::$mode = $url[0];
				$url = array_slice($url, 1);				
			}
		}
		return $url;
	}
	/** 
	 * Outputs a bunch of info on the url
	 */
	public static function dump() 
	{ 
		echo "---------------------------------------------------<br>";
		echo "URL OBJECT PROPERTIES <br>";
		echo "---------------------------------------------------<br>";
		echo "<strong>abso</strong>: ".URL::$abso."<br>";
		echo "<strong>domain</strong>: ".URL::$domain."<br>";
		echo "<strong>path</strong>: ".URL::$path."<br>";
		echo "<strong>current directory</strong>: ".URL::$cd."<br>";
		echo "<strong>relative to root</strong>: ".URL::$rel."<br>";
		echo "<br><strong>Directory Array</strong>: \n<pre>\n", print_r(URL::$dirs), "\n</pre>\n";
		echo "<br><strong>Params</strong>: \n<pre>\n", print_r(URL::$params), "\n</pre>\n";
		echo "---------------------------------------------------<br>";
	}
	
	public static function paramIsSet($params)
	{
		if (array_key_exists($params, self::$params)) {
			return true;
		}
		return false;
	}
	public static function cleanurl($url)
	{
		$url = strtolower(preg_replace('/[^a-z|0-9| ]/i','',$url));
		$url = trim(eregi_replace(" +", " ", $url)); 
		$url = preg_replace("[ ]","-", $url);
		return $url;
	}
	
	public static function checkDuplicate($table, $url, $id='url') 
	{ 
		$duplicate = new Crud($table);
		$dup = $duplicate->_where("$id = $url")
					->read('row')
		;		
		if ($dup) {
			$return = true;
		} else {
			$return = false;
		}
		$duplicate = null;
		$dup = null;
		
		return $return;
	}
	//------------------------------------------------------
	//               Internal
	//------------------------------------------------------
	
	private static $_array;
	
	//sets the path string property
	private static function setPath() 
	{ 
		//remove the leading slash from the string		
		$path = $_SERVER['REQUEST_URI'];
		
		//leave the raw url path untouched (BE CAREFUL WHERE YOU USE THIS!)
		self::$raw = $path;
		
		//clean up the path for all url properties except raw
		$path = self::clean($path);
		
		//Remove the Leading slash returned by REQUEST_URI
		$path = substr_replace($path, "", 0, 1);

		//turn path into an array to remove any params
		$array = explode("?", $path);
		
		//set the public path property
		self::$path = $array[0];
		
		//set the params property
		self::setParams($array);
	}
	
	/** 
	 * Clean Incoming URL String
	 * Called self::setPaths()
	 * Removes all characters Except (commas removed as well...):
	 * a-z (lowercase only), 1-9, &, ?, =, -, /
	 */
	private static function clean($url)
	{
		//allowed characters
		$url = strtolower(preg_replace('/[^a-z|0-9|\/|&|.|?|=|\-]/i','',$url));
		return $url;
	}
	
	//set the cd string proprty (current directory)
	private static function setCD() 
	{ 
		$c = count(self::$dirs);
		
		//if the dir array is larger then 1 entry
		if ($c > 1) {
			//deincrement the count by two
			//one for array count and one for previous entry
			$j = $c-2;
			
			//set cd property
			self::$cd = self::$dirs[$j];
		}
		//otherwise set cd property to empty string
		else {
			self::$cd = "";
		}
	}
	
	private static function setURLArray() 
	{ 
		
	}
	
	//set the abso string property
	private static function setAbso() 
	{ 
		//add domain and path properties to get abso string
		self::$abso = self::$domain.self::$path;
	}
	
	//set directory array property
	private static function setDirs() 
	{ 
		//blow up the dirs property to get dir array property
		self::$dirs = explode("/", self::$path);
		
	}

	//sets domain property
	private static function setDomain() 
	{ 
		//set the domain on the sername and add a slash
		self::$domain = $_SERVER['SERVER_NAME']."/";
	}
	
	private static function setRel() 
	{ 
		$c = count(self::$dirs);
		//in the dir property if there is....
		
		//only 1 entry
		if ($c == 1) {
			self::$rel = "./";
		}
		//there is more then one entry
		else {
			//remove one count
			$j = $c-1;
			$tmp = "";
			for($i=0; $i < $j; $i++) { 
				$tmp .= "../";
			}
			self::$rel = $tmp;
		}		
	}
	
	//set the params property
	private static function setParams(Array $params) 
	{ 		
		//set included params
		if (isset($params[1])) 
		{
			$param_str = $params[1];
			
			//turn all params into array
			$param_array = explode("&", $param_str);
			
			//break apart each param
			$c = count($param_array);
			$param_val_array = array();
			
			//extract all key and values to array
			for($i=0; $i < $c; $i++) { 
				$param_val_array[] = explode("=", $param_array[$i]);
			}
			
			//now make the final array
			$param_final_array = array();
			for($i=0; $i < $c; $i++) { 
				//make sure the values are set first...
				if (isset($param_val_array[$i][0])) {
					$key = $param_val_array[$i][0];
				}
				if (isset($param_val_array[$i][1])) {
					$value = $param_val_array[$i][1];	
				}
				if (!isset($value)) {
					$value = '';
				}
				$param_final_array[$key] = $value;
			}
			//finally set the param proprty
			self::$params = $param_final_array;
		}
		
		//no params
		else 
		{
			self::$params = array();
		}
	}
	private static function setProtocol() 
	{ 
		self::$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
	}
	
}
?>