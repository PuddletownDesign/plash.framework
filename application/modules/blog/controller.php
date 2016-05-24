<?php
/** 
 * Browse Controller Class
 */
class Blog extends Controller
{	
	/** 
	 * Blog List (Blog index)
	 */
	public function index()
	{		
		//get the section data
		$global = new Globals();
		$section = $global->getSectionData('blog');
		$this->view
			->assign('section', $section);
		
		//get categories list
		//$categories = new Categories('categories');
		//$categories = $categories->getList();
		
		
		//get the blog data
		$data = $this->model->getPagedList(4);
				
		$this->view
			//->assign('categories', $categories)
			->assign('posts', $this->filter->display($data['posts']))
			->assign('paging', $data['paging'])
			->template('list')
		;
	}
	
	/** 
	 * Get RSS Feed
	 */
	public function rss()
	{
	    $data = $this->model->getList();
	
		$this->view
			->assign('posts', $this->filter->rss($data))
			->template('rss')
		;
	}
	
	/** 
	 * Get A Single Blog Post
	 */
	public function getPost()
	{
		$comments = new Comments('blog');
		
		//add a new comment
		if (isset($_POST['new-comment'])) {
			$comments->newComment();
		}
		
		//get post data
		$data = $this->model->getPost();	
		if (!$data) {
			$error = new Globals();
			$error->error404();
		} 
		
		//get comment list
		//$comments = $comments->getList($data['id']);
				
		//
		$this->view
			//->assign('comments', $comments->getPostComments($data['id']))
			->assign('post', $this->filter->display($data))
			->template('post')
		;
		
	}
	
	/** 
	 * Edit Post 
	 */
	public function editPost()
	{
		$this->checkAuth(5);
		//check to see if form has been submitted
		if (isset($_POST['edit'])) {
			//check post data to see if required fields are complete
			$data = $this->filter->validateEdit($_POST);			
  
			//if form passed validation then submit it and redirect
			if (!isset($data['error'])) {
				$_POST = $this->filter->update($data);
				
				$data = $this->model->editPost();
				$post = 'blog/'.$data['url'];
				
				Cache::delete($post);
				
				$redirect = 'blog/'. URL::segment(2);
				Url::redirect($redirect);
			}
		} 
		
		else {
			$data = $this->model->getPost();
			if (!$data) { $this->error();}
		}
		$this->view->assign('post', $this->filter->forms($data));
		$this->view->template('edit');

	}
		
	
	/** 
	 * Get A Single Post
	 */
	public function deletePost()
	{
		$this->checkAuth(5);
		
		if (isset($_POST['delete'])) {
			$this->model->deletePost();
			
			Cache::delete('blog/');
			
			Url::redirect('blog/');
		}
		$data = $this->model->getPost();
		if (!$data) {
			$this->error();
		}
		$this->view->assign('post', $this->filter->single($data));
		$this->view->template('delete');

	}
	
	/** 
	 * Make a new Post
	 */
	public function newPost()
	{
		$this->checkAuth(5);
		
		//check to see if form has been submitted
		if (isset($_POST['new'])) {
			//check post data to see if required fields are complete
			$data = $this->filter->validateCreate($_POST);	
			
					
			//if form passed validation then submit it and redirect
			if (!isset($data['error'])) {
				$_POST = $this->filter->create($data);
				
				$data = $this->model->createPost();
				
				Cache::delete('blog/');
				
				$redirect = 'blog/'. $data['url'];
				Url::redirect($redirect);
			} 
			//assign the returned data array back to the template
			else {
				$this->view->assign('post', $data);
			}
		}
		$this->view->template('new');
	}	
}

?>