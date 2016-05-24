<?php

class Blog_Filter extends Filter
{
	//------------------------------------------------------
	//               Validation
	//------------------------------------------------------
	
	
	/** 
	 * Validate Insert Fields
	 */
	
	public function validateCreate(Array $data)
	{
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
	
	/** 
	 * Validate Update Fields
	 */
	public function validateEdit(Array $data)
	{
	    //check text
		$rules = '{
			"text"  : {
				"required"             : true,
				"required_error"       : "Please enter some text"
			}
		}';
		
		return $this->validate($rules, $data);
	}
	
	
	//------------------------------------------------------
	//               DB Input Filters
	//------------------------------------------------------
	
	/** 
	 * add and manipulate post data directly
	 */
	public function create($input)
	{	
		$this->data = $input;	
		$auth = Auth::getInstance();
		
		$this
			 ->filter('*', 'htmlspecialchars_decode')
			 ->add('created', date('Y-m-d G:i:s'))
			 ->add('author', $auth->user['id'])
			 ->add('url', Url::cleanurl($this->data['title']))	
		;
			
		return $this->data;
	}
	
	public function update($input)
	{
		$this->data = $input;
		
		$this
			->filter('*', 'htmlspecialchars_decode')
			->add('updated', date('Y-m-d G:i:s'))
		;
		return $this->data;
	}
	
	//------------------------------------------------------
	//               Output Filters
	//------------------------------------------------------
	

	
	/** 
	 * Filter Output from Database
	 */
	
	public function forms($data)
	{
		$this->data = $data;
		
		$this
			 ->filter('*', 'htmlspecialchars')
		;
		if (Url::segment(3) == 'edit') {
			$this
				 
			;
		}
			
		return $this->data;
	}

	/** 
	 * Single
	 */
	public function display($data)
	{	
		$this->data = $data;
		
		$this
			 ->filter('title', 'Typography::smartypants')
			 ->filter('text', 'Typography::markdown', 2, false, '', true)
			 ->filter('description', 'Typography::smartypants')
			 ->filter('created', 'DateHelper::format', 1)
			 ->filter('updated', 'DateHelper::format', 1)
		;
					
		return $this->data;
	}
	
	/** 
	 * RSS Filter
	 */
	public function rss($data)
	{
		$this->data = $data;
		
		$this
			 ->filter('title', 'Typography::smartypants')
			 ->filter('text', 'Typography::markdown', 2, false, '', true)
			 ->filter('description', 'Typography::smartypants')
			 ->filter('created', 'DateHelper::format', 'r')
			 ->filter('updated', 'DateHelper::format', 'r')
		;
			
		return $this->data;
	}
}
?>