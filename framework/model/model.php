<?php

abstract class Model
{
	protected $crud, $filter, $data = array();
	
	public function __construct($table){
		$this->crud = new Crud($table);
	}
}


?>