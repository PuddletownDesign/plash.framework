<?php
namespace Plash;

abstract class Controller
{
	
	protected $model, $controller, $view, $data;
	

	public function __construct($table="") 
	{		
		
		$this->controller = get_class($this);
		$this->model = $this->controller.'_Model';
		$this->filter = $this->controller.'_Filter';
		
		$module = strtolower($this->controller);
		
		if (!$table) {
			$table = $module;
		}
		
		//Set Up Post Data
		if (isset($_POST)) {
			$this->data = $_POST;
		}
		
		//each of these files should be included in the module folder
		$controller_file = APP."modules/$module/controller.php";
		$model_file = APP."modules/$module/model.php";
		$filter_file = APP."modules/$module/filter.php";
		
		//check to see if each is included and include if they are present
		//flag if they are not present
		if (is_file($model_file)) {
			include $model_file;
			$this->model = new $this->model($table);
			
		}
		
		//load filter
		if (is_file($filter_file)) {
			include $filter_file;
			$this->filter = new $this->filter();
		}

		//load authentication info if it exists
		
		if (class_exists('Auth')) {
			$auth = Auth::getInstance();
			$this->user = $auth->getUser();
		}
		
		//load view
		if (is_dir(APP."modules/$module/views/")) {
			$this->view = new View("$module/views/");	
			$this->view->assign('user', $this->user);
			
		}
	}
	
	/** 
	 * Quick check auth from controller
	 * if auth is not met serve a 404
	 */
	protected function checkAuth($level=5)
	{
	    if ($this->user['priv'] < $level) {
	    	$this->error();
	    }
	}
	
	/** 
	 * Throws a 404
	 */
	protected function error()
	{
	    $error = new Globals();
		$error->error404();
	}
} 


?>