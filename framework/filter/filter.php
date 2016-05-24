<?php
include dirname(__FILE__).'/filter_interface.php';
include dirname(__FILE__).'/filter_validate.php';
abstract class Filter extends Filter_Validate
{
	protected $data;
	
	//stores the passed filter arguments
	private $args;
	
	//stores the list of user loaded plugins
	protected $plugins;
	
	/** 
	 * Filter
	 * @param varible length
	 */
	public function filter()
	{
		/** 
		 * This method defaults as the constructor before php 5.3.3
		 * so if the data array has not been passed yet exit this function
		 */
	    if (!isset($this->data)) {
	    	return;
	    }
		
		/** 
		 * Get the list of arguments
		 */
		$this->args = func_get_args();
		
		//recursively iterate the array and filter by key
		$this->data = $this->recursiveFilter($this->data, $this->args); 
		
		return $this;
	}
	
	
	protected function add($key, $value='')
	{
	    $this->data[$key] = $value;
		return $this;
	}
	
	/** 
	 * Adds an element to the data array
	 * @param key [string]
	 * @param value [string]
	 */
	protected function add_filterInterface()
	{
		$args = func_get_args();
		$key = $args[0];
		$value = isset($args[1]) ? $args[1] : '';
		
		//add the key and value to the data array
		$this->data[$key] = $value;
	    
		//run the filter if there is a function in the params (param 3)
		$function = isset($args[2]) ? $args[2] : '';
		
		if ($function) {
			//get params	
			if (isset($args[3])) {
				$params = array_splice($args, 3, count($args));
				$params = isset($params[0]) ? implode(", ", $params) : '';
				$this->filter($key, $function, $params);
			}
			else {
				$this->filter($key, $function);
			}
		}
		return $this;
	}
	
	/** 
	 *  Recursively Filter array keys
	 * 
	 * Pass the data array by reference ()
	 */
	private function recursiveFilter(Array &$data, Array $args)
	{
		$return = array();
		foreach($data as $key => $value) {
			if ($key === $args[0]) {
				$value = $this->filterData($value, $args);
			} 
			//if 
			elseif($key === '*' || $key === ':all') {
				$value = $this->filterData($value, $args);
			}
			
			if (is_array($value)) {
				$value = $this->recursiveFilter($value, $args);
			} 
			
			$return[$key] = $value;
		}
		
		return $return;
	}
	
	/** 
	 * Filters a string with different filter types
	 */
	private function filterData($string, Array $args)
	{
		$key = $args[0];
		$function = $args[1];
		
		$params = array_splice($args, 2, count($args));
		
		$params = isset($params[0]) ? implode(", ", $params) : '';
		
	    /** 
	      branch processing to the 3 types of filters  
	      - built in function, static method, object instance plugin
	     */
	
		//run as object (assign to plugins array)
		if (strstr($function, "->")) {
			return $this->filterWithPlugin($function, $string, $params);
		} 
		
		//run as a static function
		elseif(strstr($function, "::")) {
			return $this->filterWithStaticMethod($function, $string, $params);
		}
		
		//otherwise run as a built in function
		else {
			return $this->filterWithBuiltInFunction($string, $function, $params);
		}
	}
	
	private function filterWithPlugin($function, $string, $params)
	{
	
		$classarr = explode($function, '->');
		$class = $classarr[0];
		$instance = strtolower($classarr[0]);
		$method = $classarr[1];
		
	    //if there is no plugin object created yet
		if (!is_object($this->plugins)) {
			$this->plugins = new Object();
		}
		//if there is not already an instance of this plugin create one
		if (!is_object($this->plugins->$instance)) {
			$this->plugins->$instance = new $class();
		}		
		
		//now filter the proper key with an instance
		if (!$params) {
			return $this->plugins->$instance->$method($string);
		} else {
			return $this->plugins->$instance->$method($string, $params); 
		}
	}
	
	/** 
	 * Filter String with Static Method
	 * 
	 * @param string
	 * @param string
	 * @param string
	 * 
	 * @return string
	 */
	private function filterWithStaticMethod($function, $string, $params)
	{
		return !$params ? call_user_func($function, $string) : call_user_func($function, $string, $params);
	}
	
	/** 
	 * Filter String with Built in Function
	 * 
	 * @param string
	 * @param string
	 * @param string
	 * 
	 * @return string
	 */
	private function filterWithBuiltInFunction($string, $function, $params)
	{
		return !$params ? $function($string) : $function($string, $params);

	}
}

?>