<?php
/** 
 * Browse Controller Class
 */
class Pages extends Controller
{	
	public function listPlates()
	{
		$data = $this->model->getList();
	    $this->view
			->assign('plates', $data)
			->template('list')
		;
	}

	
	/** 
	 * Get A Static Page
	 */
	public function getPage()
	{
	    $data = $this->model->getPage();
		$this->view->assign('post', $data)
		           ->template('page');
	}
	
	/** 
	 * Edit Post 
	 */
	public function edit()
	{
	    $data = $this->model->getPost();
		$this->view->assign('post', $data);
		$this->view->template('edit');
	}
	
	/** 
	 * Get A Single Post
	 */
	public function delete()
	{
	    $data = $this->model->getPost();
		$this->view->assign('post', $data);
		$this->view->template('delete');
	}
	
	/** 
	 * Make a new Post
	 */
	public function newPage()
	{
		//check to see if form has been submitted
		if (isset($_POST['new'])) {
			//check post data to see if required fields are complete
			$data = $this->filter->checkNew();			
			
			//if form passed validation then submit it and redirect
			if ($data['error']=== false) {
				$this->filter->createFilter();
				$data = $this->model->newPage();
				
				$redirect = 'http://'. URL::$domain . $data['url'];
				
				header("Location: $redirect");
				exit;
			} else {
				$this->view->assign('post', $data);
			}
		}
		$this->view->template('new');
	}
	
	public function login()
	{
		
		if (isset($_POST['login'])) {
			$users = new Users();
			$user = $users->checkLogin();
			
			if (isset($user['error'])) {
				$this->view->assign('error', 'Your username or password is incorrect');
			} else {
				$auth = Auth::getInstance();
				$auth->setUser($user);
				$redirect = 'http://'.Url::$domain.'/blog/';
				header("Location: $redirect");
				exit;
			}
		}
		$data = $this->model->getPage();
		
		$this->view->assign('post', $data);
		Cache::enable(false);
	    $this->view->template('login');
	}
	public function logout()
	{
	    $auth = Auth::getInstance();
		$auth->logout();
		
		$last_page = $_SERVER['HTTP_REFERER'];
				
		header("Location: $last_page");
		exit;
	}

}


?>