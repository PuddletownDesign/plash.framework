<?php
/** 
 * Browse Controller Class
 */
class Home extends Controller
{	
	/** 
	 * Home page List
	 */
	public function index()
	{
		$this->view->template('index');
	}
}


?>