<?php

/** 
 * Filter Validate Class
 * 
 * Filers a flat array of data to a set of rules defined in a json object
 * 
 * Current Supported Rules are:
 * required, min_length, max_length, check_dup, email
 * 
 * to set an error message for any of these add _error to the end of a key
 * 
 * example: 
		'title' = {
			'required' : 1,
			'required_error' : 'you suck'
		}
 * 
 * CheckDup Rule is Dependent on URL Class
 * define checkdup rule as follows
   	"title" : {
		"check_dup"            : "Table Column",
		"check_dup_error"      : "There is already a post with this title"
	},
 */
class Filter_Validate
{
	//------------------------------------------------------
	//               Validation Methods
	//------------------------------------------------------
	
	/** 
	 * Validate Methods
	 * @param $string STRING
	 * @param $rule MIXED
	 * @return boolean
	 */
	private function required($string, $rule)
	{
	    return $string ? true : false;	
	}
	
	private function min_length($string, $rule)
	{
	    return strlen($string) > $rule ? true : false;	
	}
	
	private function max_length($string, $rule)
	{
	    return strlen($string) < $rule ? true : false;
	}
	
	private function email($string, $rule)
	{
	    return filter_var($string, FILTER_VALIDATE_EMAIL) ? true : false;
	}
	
	private function check_dup($string, $rule)
	{
	    $rules = explode(" ", $rule);
		return URL::checkDuplicate($rules[0], URL::cleanurl($string), $rules[1]) ? false : true;
	}
	
	
	//------------------------------------------------------
	//               Interface
	//------------------------------------------------------
	
	
	/** 
	 * Validate Post data to json rules
	 * 
	 * @param $rules (json data)
	 * @param $data (Array) default = $_POST
	 * @return ARRAY
	 */
	protected function validate($rules, $data = '')
	{
		//if the data param is not defined use the POST array directly
		if (!$data) {
			$data = $_POST;
		}
		
		//decode the json rules
	    $rules = json_decode($rules, true);
		
		//for each key in the rules array
		foreach($rules as $data_key => $data_value) {
			//check to see if the key matches a known rule
			foreach($rules[$data_key] as $rule_key => $rule_value) {
				//and run the corrisponding function
				if (!strstr($rule_key, "_error")) {
					if (!$this->$rule_key($data[$data_key], $rule_value)) {
						$data[$data_key.'_error'] = $rules[$data_key][$rule_key.'_error'];
						$data['error'] = true;
					}
				}	
			}
		}
		return $data;
	}
	
	
}


?>