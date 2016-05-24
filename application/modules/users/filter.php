<?php

class Users_filter extends Filter
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
		
		//check username
	    if (isset($_POST['user'])) {
			$user = $_POST['user'];
			$cleaned_user = URL::cleanurl($user);
			
	    	if ($user == '') {
	    		$post['user_error'] = "you need to enter a username";
				$post['error'] = true;
	    	}
			elseif($user !== $cleaned_user) {
				$post['user_error'] = "Username can only contain lowercase, numbers and dashes";
				$post['error'] = true;
			}
			
			elseif(URL::checkDuplicate('Users', $cleaned_user, 'user')) {
				$post['title_error'] = 'this username has been taken';
				$post['error'] = true;
			}
			$post['user'] = $cleaned_user;
	    }
	
		//check real name
		if (isset($_POST['name'])) {
			$name = $_POST['name'];
			if ($name == '') {
	    		$post['name_error'] = "you need to enter a name";
				$post['error'] = true;	
	    	}
			$post['name'] = $name;
		}
		
		//check password
		if (isset($_POST['password'])) {
			$password = $_POST['password'];
			if ($password == '') {
	    		$post['password_error'] = "Enter a password";
				$post['error'] = true;	
	    	}
			
			if($password !== $_POST['password_check']) {
				$post['password_error'] = "The two passwords do not match";
				$post['error'] = true;
			} 
			unset($_POST['password_check']);
			
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
		$_POST['user'] = URL::cleanurl($_POST['user']);
		
		$auth = Auth::getInstance();
		$_POST['password'] = $auth->encryptPassword($_POST['password']);		
	}
    
	public function editFilter()
	{
	}

    
	//------------------------------------------------------
	//               Output Filters
	//------------------------------------------------------
    
	/** 
	 * RSS Filter
	 */
	public function rss($data)
	{
		return $data;
	}
    
	/** 
	 * Filter Output from Database
	 */
    
	public function edit($data)
	{
	    return $data;
	}
    
	public function lists($data)
	{
	    $count = count($data);
		if ($count==0) {return false; } 
    
		for($i=0; $i < $count; $i++) { 
			switch ($data[$i]['priv']) {
				case 1:
					$data[$i]['priv'] = 'Guest';
				break;
				case 2:
					$data[$i]['priv'] = 'Author';
				break;
				case 3:
					$data[$i]['priv'] = 'Client';
				break;
				case 4:
					$data[$i]['priv'] = 'Author';
				break;
				case 5:
					$data[$i]['priv'] = 'Admin';
				break;
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
		$data['text'] = Typography::markdown($data['text'], 2, true, '', true);
		$data['description'] = htmlspecialchars($data['description']);
    
		return $data;
	}
}

?>