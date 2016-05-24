<?php

class Comments_Filter extends Filter
{
	//------------------------------------------------------
	//               Validation
	//------------------------------------------------------
	
	
	/** 
	 * Validate Insert Fields
	 */
	
	public function checkNew()
	{
		$post = $_POST;
		$post['error'] = false;
		//check title
	    if (isset($_POST['name'])) {
			$name = $_POST['name'];
	    	if ($name == '') {
	    		$post['name_error'] = "you need to enter your name";
				$post['error'] = true;
	    	}
			$post['name'] = $name;
	    }
		//check text
		if (isset($_POST['email'])) {
			$email = $_POST['email'];
	    	if ($email == '') {
	    		$post['email_error'] = "please enter your email address";
				$post['error'] = true;
	    	}
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$post['email_error'] = "This is not a valid email address";
				$post['error'] = true;
			}
			$post['email'] = $email;
	    }
	
		if (isset($_POST['comment'])) {
			$comment = $_POST['comment'];
	    	if ($email == '') {
	    		$post['comment_error'] = "please add a comment";
				$post['error'] = true;
	    	}

			$post['comment'] = $comment;
	    }
		return $post;
	}
	
	/** 
	 * Validate Update Fields
	 */
	public function checkEdit()
	{
	    //check text
		$post = $_POST;
		$post['error'] = false;
		$text = $_POST['text'];
	    
		if ($text == '') {
			$post['text_error'] = "you need to enter some text";
			$post['error'] = true;
		}
		if (isset($_POST['enable_comments'])) {
			$post['enable_comments'] = 1;
		}
		//if there is any error add values back to form
		$post['text'] = $text;
		$post['url'] = $_POST['edit'];
	    
		return $post;
	}
	
	
	//------------------------------------------------------
	//               DB Input Filters
	//------------------------------------------------------
	
	/** 
	 * add and manipulate post data directly
	 */
	public function createFilter($defaults = array())
	{
		$_POST['name'] = filter_var($name, FILTER_SANITIZE_STRING);
	    $_POST['created'] = date('Y-m-d G:i:s');
		$_POST['url'] = URL::cleanurl($_POST['title']);
		
		$auth = Auth::getInstance();
		$_POST['author'] = $auth->user['id'];
	}
	
	public function editFilter()
	{
		$_POST['updated'] = date('Y-m-d G:i:s');
		$_POST['description'] = htmlspecialchars_decode($_POST['description']);
		$_POST['text'] = htmlspecialchars_decode($_POST['text']);
	}
	
	//------------------------------------------------------
	//               Output Filters
	//------------------------------------------------------
	
	/** 
	 * RSS Filter
	 */
	public function rss($data)
	{
		$count = count($data);
		if ($count==0) {return false; } 
		
		for($i=0; $i < $count; $i++) { 
			$data[$i]['title'] = Typography::smartypants($data[$i]['title']);
			$data[$i]['description'] = Typography::smartypants($data[$i]['description']);
			$data[$i]['created'] = DateHelper::format($data[$i]['created'], 'r');
		}
		
		return $data;
	}


	public function lists($data)
	{
	    $count = count($data);
		if ($count==0) {return false; } 
		
		for($i=0; $i < $count; $i++) { 
			$data[$i]['title'] = Typography::smartypants($data[$i]['title']);
			$data[$i]['description'] = Typography::smartypants($data[$i]['description']);
			$data[$i]['created'] = DateHelper::format($data[$i]['created'], 1);
					
			//if (URL::segment(2)=='page') {
			//	$data[$i]['url'] = '../'.$data[$i]['url'];
			//}
		}
		
		return $data;
	}
	/** 
	 * Single
	 */
	public function single($data)
	{
		$count = count($data);
		if ($count==0) {return false; }
		
		//$header_level=2, $xss=true, $link_rel='no-follow', $use_smarty=true
		$data['title'] = Typography::smartypants($data['title']);
		$data['text'] = Typography::markdown($data['text'], 2, false, '', true);
		$data['description'] = Typography::smartypants($data['description']);
		$data['created'] = DateHelper::format($data['created'], 1);
		if ($data['updated']) {
			$data['updated'] = DateHelper::format($data['updated'], 1);
		}
		
		return $data;
	}
}
?>