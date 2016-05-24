<?php


class Routing
{
	
	//private current url properties
	private static $urlArray, $urlArrayCount;
	
	//private custom routing properties
	private static $routes = null, $customRoutesCount;
	
	const DEFAULT_CLASS = 'Home';
	const DEFAULT_METHOD = 'index';
	
	public static $class, $method, $template, $module;
	

	/** 
	 * returns object->method string set in the routing array
	 * sets Routing::
	 * @param  Array
	 * @return Array
	 */
	public static function match()
	{
		self::$urlArray = URL::$dirs;
		
		
		$routing_file = APP."routing.php";
		if (!is_file($routing_file)) {
			echo "$routing_file does not exist";
			exit;
		}
		include $routing_file;
		
		self::$routes = $routing;
		
		//if the custom routing array has been set
		
		$routing = self::getSameLengthRoutes(self::$routes);			
		
		//match return the action associated with the match
		$match = false;
		$match = self::matchCustomUrlParams($routing);	
		if ($match) {
			$class = Routing::$class;
			$method = Routing::$method;
			$app = new $class();
			$app->$method();
		}
		else {
			$globals = new Globals();
			$globals->error404();
		}
	}
	
	private static function setClassAndMethodName($route) 
	{ 
		$array = explode('->', $route);
		self::$class = $array[0];
		self::$method = $array[1];
		self::$module = strtolower(self::$class);
		self::$template = strtolower(self::$method);
	}

	
	/** 
	 * reduce the number of routing arrays to only the same length
	 * @param Array
	 * @return Array
	 */
	private static function getSameLengthRoutes() 
	{ 	
		self::$urlArrayCount = count(self::$urlArray);
		$routing = array();
		$sameLengths = array();
		
		//set the total number of custom routes
		$i = 0;
		foreach(self::$routes as $key => $value) {
			$routing[$i]['action'] = $value;
			$routing[$i]['url'] = explode('/', $key);
			//move the same length arrays to a new array
	
			if (self::$urlArrayCount == count($routing[$i]['url'])) {
				$sameLengths[] = $routing[$i];
			}
			$i++;
		}		
		return $sameLengths;
	}
	
	
	
	
	/** 
	 * returns object->method string set in the routing array
	 * sets Routing::
	 * @param  Array
	 * @return STRING
	 */
	private static function matchCustomUrlParams($routing) 
	{ 	
		$match = false;
		//loop the same lengths array
		$count = count($routing);
		for($i=0; $i < $count ; $i++) { 
			//loop the url parameters
			for($k=0; $k < self::$urlArrayCount; $k++) { 
				
				//set match to false at the beginning of each url check
				$match = false;
				$routing_url = $routing[$i]['url'][$k];
				
				//check to see if it is a normal keyword match
				if ($routing_url == self::$urlArray[$k]) {
					$match = true;
				}
				//check to see if it is a match any
				elseif ($routing_url == ':any') {
					$match = self::matchAny(self::$urlArray[$k]);
				}
				//check to see if the param is an int match
				elseif($routing_url == ':int') {
					$match = self::matchInt(self::$urlArray[$k]);
				} 
				//if not a match of keyword, int, all then,
				//move onto next array
				else {
					break;
				}
				
				//if it is the last item AND all items have matched
				//then return the associated action
				$j = self::$urlArrayCount-1;
				if ($k == $j AND $match) {
					$route = $routing[$i]['action'];
					
					self::setClassAndMethodName($route);
					
					return $route;
					break 2;
				}
			}
		}
		
		//if there 
		if ($match==false) {
			return false;
		}
	}
	
	
	
	
	
	
	//------------------------------------------------------
	//               Matching Functions
	//------------------------------------------------------
	
	
	/** 
	 * Map to regex Match strings
	 * @param String
	 * @return Boolean
	 */
	private static function matchAny($url) 
	{ 	
		return preg_match('/(^[a-z0-9\-]+$)/', $url);	
	}
	
	/** 
	 * Map to regex Match numbers
	 * @param String
	 * @return Boolean
	 */
	private static function matchInt($url) 
	{ 
		return preg_match('/(^[0-9]+$)/', $url);
	}
}


?>