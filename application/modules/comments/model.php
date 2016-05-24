<?php

class Comments_Model extends Model
{	
	public $table;
	
	public function __construct($table) 
	{
		parent::__construct($table);
	}
	
	public function getPostComments($url)
	{
		return $this->crud->_where("post = $url")
					->read()
		;
	}
	
	/** 
	 * Creates A Post
	 */
	public function createComment()
	{
	    return $this->crud->columns('created, name, text, email')
	                      ->create();
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
	public function getPost()
	{
	    $url = URL::segment(2);
		return $this->crud->_where("url = $url")
					->read('row')
		;
	}
	
	/** 
	 * Updates a Post
	 */
	public function editPost()
	{
		/* only set form values to be posted from form
		you can insert the rest in the input filter */

		return $this->blog->columns('text, description, tags, category')
		                  ->_where('url = '. $_POST['edit'])
		                  ->update()
		;
	}
	

	
	/** 
	 * deletes A post
	 */
	public function deletePost()
	{
	    return $this->blog->where('url = '.$_POST['delete'])
						  ->delete();
	}
}


?>