<?php

class Categories_Model extends Model
{
	public $table;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->crud = new Crud($this->table);
	}
	
	public function getList()
	{
		return $this->crud->orderby('id DESC')
					->read()
		;	
	}
}

?>