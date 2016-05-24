<?php

class Blog_Model extends Model
{	
	
	public function __construct($table) 
	{
		parent::__construct('blog');
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
	
	public function getPagedList($c)
	{
		$posts = $this->crud
				->columns('title, url, created, description, tags, category, updated')
				->orderby('id DESC');	
		
		$pagination = new Pagination($posts);
		
		return $pagination->read($c);
	}
	
	/** 
	 * Gets A single Page Post Data
	 */
	public function getPost()
	{
		//SELECT blog.title, users.name
		//FROM blog
		//INNER JOIN users 
		//ON users.id = blog.author
		//WHERE url =  'omfg'
		//LIMIT 0 , 30
	    $url = URL::segment(2);
		return $this->crud
					->columns("title, text, description, url, created, updated, 
							   tags, category, commentcount,
						       blog.id, users.user AS author_url, users.name AS author")
					->_where("url = $url")
					->jointable('users')
					->innerjoin('users.id = blog.author')
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
		return $this->crud->columns('text, description, tags, category, updated')
		                  ->_where('url = '. URL::segment(2))
						  ->update()
		;
	}
	
	/** 
	 * Creates A Post
	 */
	public function createPost()
	{
	    return $this->crud->columns('title, text, author, created, description, tags, category, url')
	                      ->create();
	}
	
	/** 
	 * deletes A post
	 */
	public function deletePost()
	{
	    return $this->crud->_where('url = '.$_POST['delete'])
						  ->delete();
	}
}


?>