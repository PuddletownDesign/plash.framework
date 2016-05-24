<?php
//user are set on 4 levels
//0 - guest
//1 - contributer
//2 - client
//3 - moderator
//4 - admin
namespace Plash;

class Auth
{
	static $_instance;
	public $user = array();
	private $salt_length = 7;
	
	private function __construct() 
	{
		$session_dir = APP.'tmp/sessions';
		if (!is_dir($session_dir)) {
			mkdir($session_dir);
		}
		//start user session
		session_set_cookie_params(7200, '/');
		session_save_path(APP.'tmp/sessions');
		
		session_start();
		
		//if there is no existing session the user has 0 privs
		if (!isset($_SESSION['user'])) {
			$_SESSION['user']['priv'] = 0;
			$this->user = $_SESSION['user'];
		}
		//if the user session exists set the session
		else {
			if (isset($_SESSION['user']['password'])) {
				unset($_SESSION['user']['password']);
			}
			$this->user = $_SESSION['user'];
		}
	}
	
	private function __clone() {}
	
	public static function getInstance() 
	{ 
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	
	public function getUser()
	{
	    return $this->user;
	}
	
	public function setUser($user)
	{
		unset($_SESSION['user']);
	    
		$this->user = $user;
		$_SESSION['user'] = $this->user;
	}
	
	/** 
	 * Generate a new password hash
	 */
	public function encryptPassword($password) 
	{ 
		$salt = substr(md5(time()), 0, $this->salt_length);
		$password = $salt.sha1($password);
		return $password;
	}
	
	/** 
	 * Decrypt the password to compare
	 */
	private function extractHashSalt($password)
	{
	    $salt = substr($password, 0, $this->salt_length);
		return $salt;
	}
	
	public function passwordsMatch($posted, $stored)
	{
		$salt = $this->extractHashSalt($stored);
		$password = $salt . sha1($posted);
		return $password === $stored ? true : false;   
	}
	

	public function logout()
	{
		unset($_SESSION['user']);
	}
}
?>