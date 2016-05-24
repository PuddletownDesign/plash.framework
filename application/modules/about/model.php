<?php

class About_Model extends Model
{		
	/** 
	 * Gets A Post List
	 */

	public function getList()
	{
		return $this->crud
					->orderby('id DESC')
					->read();
	}
	
	public function getPost()
	{
		$url = Url::segment(2);
		return $this->crud
					->_where("url = $url")
					->read('row');
	}
	

}


?>