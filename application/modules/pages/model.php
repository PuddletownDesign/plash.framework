<?php

class Pages_Model extends Model
{	
	public function __construct() 
	{
		parent::__construct('pages');
	}
	
	
	/** 
	 * Gets A Post List
	 */
	public function getList()
	{
		return $this->crud->orderby('id DESC')
					->read(10)
		;	
	}
	
	/** 
	 * Gets A single Page Post Data
	 */
	public function getPage()
	{
	    $url = URL::segment(1);
		return $this->crud->_where("url = $url")
					->read('row')
		;
	}
	
	/** 
	 * Updates a Post
	 */
	public function updatePost()
	{
		/* only set form values to be posted from form
		you can insert the rest in the input filter */

		return $this->crud->columns('text')
		                  ->_where('url = '. $_POST['edit'])
		                  ->update()
		;
	}
	
	/** 
	 * Creates A Post
	 */
	public function newPage()
	{
	    return $this->crud->columns('title', 'text', 'created', 'description', 'url')
	                      ->create();
	}
	
	/** 
	 * Check Login Credentials
	 */
	public function login()
	{

	}
	
	/** 
	 * deletes A post
	 */
	public function deletePost()
	{
	    return $this->crud->where('url = '.$_POST['delete'])
						  ->delete();
	}
}


?>