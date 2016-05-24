<?php

interface Filter_Interface
{
	
	/** 
	 * Form Validation
	 */
	public function validateCreate(Array $data);
	
	public function validateEdit(Array $data);
	
	
	/** 
	 * Input Filters When Creating and Deleting
	 */
	public function create(Array $input);
	
	public function update(Array $input);
	
	/** 
	 * Output Filters for screen Display
	 */
	public function forms(Array $output);
	
	public function display(Array $output);
}
?>