<?php

class Users_Model extends Model
{	
	public function __construct() 
	{
		parent::__construct('users');
	}
	
	/** 
	 * Logs a user in
	 */
	public function getUser($user)
	{
	    return $this->crud->_where("user = $user")
	                      ->read('row');
	}
	
	/** 
	 * Gets A List of Users
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
	//public function getUser()
	//{
	//    $url = URL::segment(2);
	//	return $this->blog->_where("url = $url")
	//				->read('row')
	//	;
	//}
	
	/** 
	 * Updates a Post
	 */
	//public function updatePost()
	//{
	//	/* only set form values to be posted from form
	//	you can insert the rest in the input filter */
    //
	//	return $this->blog->columns('text')
	//	                  ->_where('url = '. $_POST['edit'])
	//	                  ->update()
	//	;
	//}
	
	/** 
	 * Creates A Post
	 */
	public function createUser()
	{
	    return $this->crud->columns('user', 'name', 'password', 'priv')
	                      ->create();
	}
	
	/** 
	 * deletes A post
	 */
	//public function deletePost()
	//{
	//    return $this->blog->where('url = '.$_POST['delete'])
	//					  ->delete();
	//}
}


?>