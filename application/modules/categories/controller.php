<?php

class Categories extends Controller
{
	public function __construct($table, $section="") 
	{
		parent::__construct();
		$this->model->crud->table($table);
		
		if (!$section) {
			$this->section = $table;
		} else {
			$this->section = $section;
		}
	}
	
	/** 
	 * Gets List of Categories
	 */
	public function getList()
	{
	    $data = $this->model->getList();
		return $data;
	}
}

?>