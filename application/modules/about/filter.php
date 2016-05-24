<?php

class About_Filter extends Filter implements Filter_Interface
{	
	/** 
	 * Form Validation
	 * rules are required, min_length, check_dup, email
	 */
	
	public function validateCreate(Array $data) {
		//rules are required, min_length, check_dup, email
		$rules = '{
			"title" : {
				"required"             : true,
				"required_error"       : "Enter a title",
				"check_dup"            : "Blog url",
				"check_dup_error"      : "There is already a post with this title"
			},
			"text"  : {
				"required"             : true,
				"required_error"       : "You didn&#8217;t enter any text"
			}
		}';
		
		return $this->validate($rules, $data);
	}
	
	public function validateEdit(Array $data) {
		$rules = '{
			"text"  : {
				"required"             : true,
				"required_error"       : "Please enter some text"
			}
		}';
		
		return $this->validate($data, $rules);
	}
	
	
	/** 
	 * Input Filters When Creating and Deleting
	 */
	public function create(Array $input) {
		$this->data = $input;
		
		$this
			 ->filter('*', 'htmlspecialchars_decode')
			 ->add('created', 'date', 'Y-m-d G:i:s')
		;
			
		return $this->data;
	}
	
	public function update(Array $input) {
		$this->data = $input;
		
		$this
			 ->filter('title', 'strtoupper')
			 ->filter('description', 'htmlspecialchars')
			 ->filter('description', 'str_rot13')
		;
			
		return $this->data;
	}
	
	/** 
	 * Output Filters for screen Display
	 */
	public function forms(Array $output) {
		$this->data = $output;
		
		$this
			 ->filter('title', 'strtoupper')
			 ->filter('description', 'htmlspecialchars')
		;
			
		return $this->data;
	}
	
	public function display(Array $output) {
		$this->data = $output;
		
		$this
			 ->filter('title', 'strtolower')
			 ->filter('title', 'strtoupper')
			 ->filter('description', 'htmlspecialchars')
			 ->filter('description', 'str_rot13')
			 ->filter('text', 'Typography::markdown')
			 ->filter('updated', 'DateHelper::format', 1)
			 ->filter('created', 'DateHelper::format', 1)
		;
			
		return $this->data;
	}

}
?>