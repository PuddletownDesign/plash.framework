<?php
	
class Comments extends Controller
{
	/** 
	 * @param $table - database table to read comments from
	 * @param $section - the section url (stored in sections table eg. blog) - 
	 */
	public function __construct($table, $section="") 
	{
		parent::__construct($table);
		if (!$section) {
			$this->section = $table;
		} else {
			$this->section = $section;
		}
	}
	
	/** 
	 * Get Comments for a Post
	 */
	public function getPostComments($id)
	{
	    $comments = $this->model->getPostComments($id);
		return $this->filter->commentsList($comments);
	}
	
	/** 
	 * Add a new Comment
	 */
	public function newComment()
	{		
		//check post data to see if required fields are complete
		$data = $this->filter->checkNew();			
		
		//if form passed validation then submit it and redirect
		if ($data['error']=== false) {
			$this->filter->createFilter();
			$data = $this->model->createComment();
			
			$redirect = $this->section. $data['url'];
			Url::redirect($redirect);
		} else {
			$this->view->assign('post', $data);
		}
	}
}

?>