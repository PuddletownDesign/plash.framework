<?php
/** 
 * Browse Controller Class
 */
class Users extends Controller
{	
	/** 
	 * Browse List
	 */
	//public function home()
	//{
	//	//new Scaffold('Lots');
	//	//$this->getList();
	//	$this->view->template('home');
	//}
	
	//public function listPlates()
	//{
	//	$data = $this->model->getList();
	//    $this->view
	//		->assign('plates', $data)
	//		->template('list')
	//	;
	//}
	
	/** 
	 * Get The List of Users
	 */
	public function getList()
	{		
		$data = $this->model->getList();		
		$this->view->assign('users', $this->filter->lists($data))
		           ->template('list')
		;
	}
	
	/** 
	 * Get A Single Blog Post
	 */
	//public function getPost()
	//{
	//    $data = $this->model->getPost();
	//	$this->view->assign('post', $data)
	//	           ->template('post');
	//}
	
	/** 
	 * Edit Post 
	 */
	//public function editPost()
	//{
	//    $data = $this->model->getPost();
	//	$this->view->assign('post', $data);
	//	$this->view->template('edit');
	//}
	
	/** 
	 * Get A Single User
	 */
	public function getUser()
	{
	    $data = $this->model->getPost();
		$this->view->assign('post', $data);
		$this->view->template('delete');
	}
	
	/** 
	 * Make a new User
	 */
	public function newUser()
	{
		//check to see if form has been submitted
		if (isset($_POST['new'])) {
			//check post data to see if required fields are complete
			$data = $this->filter->checkNew();			
			
			//if form passed validation then submit it and redirect
			if ($data['error']=== false) {
				$this->filter->createFilter();
				$data = $this->model->createUser();
				
				$redirect = 'http://'. URL::$domain .'user/'. $data['user'];
				header("Location: $redirect");
				exit;
			} else {
				$this->view->assign('post', $data);
			}
		}
		$this->view->template('new');
	}

	/** 
	 * Log the user in
	 */
	public function checkLogin()
	{
		//get the user record
		$user = Url::cleanurl($_POST['user']);
	    $data = $this->model->getUser($user);

		//see if the username is correct
		if (!isset($data['user'])) {
			$data['error'] = true;
		} else {
			$auth = Auth::getInstance();
			//if the passwords
			if ($auth->passwordsMatch($_POST['password'], $data['password'])) {
				return $data;
			} else {
				unset($data);
				$data['error'] = true;
			}
		}
		return $data;
	}
}


?>