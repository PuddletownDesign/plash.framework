<?php

class Globals extends Controller
{
	
	/** 
	 * get section Data
	 */
	public function getSectionData($section)
	{
	    $crud = new Crud('Sections');
		$sections = $crud
				->columns('title, url, description')
				->_where("url = $section")
				->read('row')
		;
		
		return $this->filter->sectionDataFilter($sections);
	}
	
	
	public function error404() 
	{ 
		header("HTTP/1.0 404 Not Found");
		$this->view->template('404');
		exit;
		
	}
}


?>