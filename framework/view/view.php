<?php

class View
{
	private $data_array = array();
	private $data_object;
	private $template_dir;
	private $filter;
	private $incs = array();
	
	public function __construct($template_dir='') 
	{
		if ($template_dir != '') {
			$template_dir = APP."modules/$template_dir";
			if (!is_dir($template_dir)) {
				throw new Exception("$template_dir is not a directory");
			}
			$this->template_dir = $template_dir;
		}		
	}
	
	/** 
	 * Include a View in a Template
	 * pass in module/file
	 */
	public function inc($string, $datatype='object') 
	{ 
		//fix to make more flexible
		$arr = explode("/", $string);
		$file = APP.'modules/'.$arr[0].'/views/'.$arr[1].'.php';
		
		if ($datatype == 'object') {
			foreach($this->data_object as $key => $value) {
				$$key = $value;
			}
		} else {
			foreach($this->data_array as $key => $value) {
				$$key = $value;
			}
		}
				
		if (file_exists($file)) {
			include $file;
		} else {
			throw new Exception("The file $file doesn't exist");
		}
	}
	
	/** 
	 * Assign input to the data var
	 */
	public function assign($key, $val)
	{
		$this->data[$key] = $val;
		return $this;
	}
	
	/** 
	 * Format Data as json, object or array for the template
	 */
	private function formatData($return_type)
	{
	    switch ($return_type) {
			case 'json':
				$json = json_encode($this->data);				
			break;
			
			case 'object':	
				$this->data_object = new stdClass;
				foreach($this->data as $key => $value) {
					$this->data_object->$key = $this->dataToObject($this->data[$key]);
				}
			break;	
		}
	}
	/** 
	 * View Template
	 */
	public function template($file, $return_type='object')
	{				
		//turn data arrays into local variables
		$this->formatData($return_type);
		if ($return_type == 'object') {
			foreach($this->data_object as $key => $value) {
				$$key = $value;
			}
		} else {
			foreach($this->data_array as $key => $value) {
				$$key = $value;
			}
		}		
		
		//clear data array
		$filepath = $this->template_dir . $file . '.php';
		if (is_file($filepath)) {						
			
			//if cache class is present make a cache file
			if (class_exists('Cache')) {
				Cache::start();
				require_once $this->template_dir . $file . '.php';
				Cache::stop();
			} else {
				require_once $this->template_dir . $file . '.php';
			}
			
			
			$filepath = null;
		} 
		else {
			echo "the view $filepath <br> DOESN'T EXIST";
		}
		
	}


	/** 
	 * Filters the data, assigns it back to $this->data
	 * @param array
	 * @return null
	 */

	/** 
	 * Set Template Directory
	 */
	public function templateDir($dir)
	{
	    if (!is_dir($dir)) {
	    	throw new Exception("$dir is not a valid directory");
	    }
		else {
			$this->templateDir = $dir;
		}
	}
	
	/** 
	 * Convert the Data Array to an object
	 */
	private function dataToObject($array)
	{
		$object = new STDClass();
		if (is_array($array) AND count($array) > 0) {
			foreach($array as $key => $value) {
				$key = strtolower(trim($key));
				if (array_key_exists($key, $array)) {
					$object->$key = $this->dataToObject($value);
				}
		    }
			return $object;
		}
		else {
			return $array;
		}
	}
}


?>