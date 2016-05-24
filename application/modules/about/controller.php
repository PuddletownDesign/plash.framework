<?php
/** 
 * Browse Controller Class
 */
class About extends Controller
{	
	public function __construct() 
	{
		parent::__construct('blog');
	}
	/** 
	 * Blog List (Blog index)
	 */
	
	public function index()
	{
		//get the section data
		$global = new Globals();
		$this->view
			->assign('section', $global->getSectionData('blog'));
		
		//get the blog data
		$data = $this->model->getList();
			
		echo "\n<pre>\n", print_r($this->filter->display($data)), "\n</pre>\n";
		exit;
			
		$this->view
			->assign('posts', $this->filter->display($data))
			->template('list')
		;
	}
	
	public function getPost()
	{

		//get the blog data
		$data = $this->model->getPost();
			
		$this->view
			->assign('post', $this->filter->display($data))
			->template('post')
		;
	}
	
	public function newPost()
	{
	    $array = array(
	    	"title" => "this is the title",
			"email" => "d@puddletowndesign.com",
			"text" => ""
	    );
	
		$result = $this->filter->validateCreate($array);
		echo "<br> ------------------ output --------------------";
		echo "\n<pre>\n", print_r($result), "\n</pre>\n";
		
	}
	
}

?>