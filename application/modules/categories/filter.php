<?php

class Categories_Filter extends Filter
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
	    if (isset($_POST['title'])) {
			$title = $_POST['title'];
	    	if ($title == '') {
	    		$post['title_error'] = "you need to enter a title";
				$post['error'] = true;
	    	}
			elseif(URL::checkDuplicate('Blog', URL::cleanurl($title), 'url')) {
				$post['title_error'] = 'there is already a post with the same title';
				$post['error'] = true;
			}
			$post['title'] = $title;
	    }
		//check text
		if (isset($_POST['text'])) {
			$text = $_POST['text'];
			if ($text == '') {
	    		$post['text_error'] = "you need to enter some text";
				$post['error'] = true;	
	    	}
			$post['text'] = $text;
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
	public function createFilter()
	{
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
	
	/** 
	 * Filter Output from Database
	 */
	
	public function edit($data)
	{
		$data['title'] = Typography::smartypants($data['title']);
		$data['description'] = htmlspecialchars($data['description']);
		$data['text'] = htmlspecialchars($data['text']);
		$data['created'] = DateHelper::format($data['created'], 1);
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
			
			if (URL::segment(2)=='page') {
				$data[$i]['url'] = '../'.$data[$i]['url'];
			}
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
		
		return $data;
	}
}
?>