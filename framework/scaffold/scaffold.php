<?php

class Scaffold
{
	protected $db, $table, $columns, $view_dir, $style;
	
	
	/** 
	 * Constructor
	 * @param String, [DB object]
	 * @return null
	 */
	public function __construct($table, $id='id', $db=null) 
	{
		$this->style = 'dark';
		$this->table = $table;
		$this->id = $id;
		if ($this->table==null) {
			throw new Exception("set a table in the scaffold constructor");
		}
		if ($db==null) {
			$this->db = Database::getInstance();
		}
		$this->view_dir = dirname(__FILE__).'/views/';
		$this->route();
		exit;
	}
	
	
	/** 
	 * Routes the 4 view types
	 */
	protected function route()
	{
		//get the edit form
		if(isset($_GET['edit'])) {
			$this->edit();
		}
		//get the new post form
		elseif (isset($_GET['new'])) {
	    	$this->insert();
	    }
		//get the delete page
		elseif (isset($_GET['delete'])) {
	    	$this->delete();
	    }
		//get table data
		else {
			$this->getlist();
		}
	}	
	
	
	/** 
	 * Get the list of all table rows
	 * @param 
	 * @return 
	 */
	protected function getlist()
	{
		//query the table
		$query = "SELECT * FROM {$this->table} ORDER BY {$this->id} DESC";
		try 
		{
			$result = $this->db->prepare($query);
			$result->execute();
		}
		catch(Exception $e) { 
			echo "Scaffolding Table: '{$this->table}'  not found";
			exit;
		}
		while ($row = $result->fetchAll(PDO::FETCH_ASSOC)) {
			$post = $row;
		}		
		include $this->view_dir.'/list.php';	
	}
	
	
	/** 
	 * 
	 * @param Edit Controller Method
	 * @return 
	 */
	protected function edit()
	{
		//check to see if database supports datatypes
		$meta = $this->supportsDatatypes();
		
		//post the edit to the db
		if (isset($_POST['edit'])) {
			if ($meta) {
				$post = $_POST;
				$post[$this->id] = $post['edit'];
				$valid = $this->checkInsert($meta);
				
				//if there is no form checking error then post and redirect
				if (!$valid['error']) {
					$this->postEdit($meta);
					$this->redirect();
				}
			}
		}
		//get post edit form
		else {
			if (empty($_GET['edit'])) {$this->redirect();}
			//query the table
			$post = $this->getRow($_GET['edit']);
		}
		//truncate the post
		include $this->view_dir.'/edit.php';	
		
	}
	
	
	/** 
	 * COntroller Insert method
	 * @param 
	 * @return Null
	 */
	protected function insert()
	{
		//if the database support column type values, then get them to format the insert form
		$meta = $this->supportsDatatypes();
		if (isset($_POST['new'])) {
			//if the database supports data types then check the for
			if ($meta) {
				$vaild = $this->checkInsert($meta);
				//if there is no form checking error then post and redirect
				if (!$vaild['error']) {
					$this->postInsert($meta);
					$this->redirect();
				}
			}
			else {
				//add a generic post insert here for use with sqlite and such
				//$this->postCreate();
			}
		}
		include $this->view_dir.'/new.php';	
		
	}
	
	/** 
	 * Controller Delete methods
	 * @return null
	 */
	protected function delete()
	{
		if (isset($_POST['delete'])) {
			$query = "DELETE FROM {$this->table} WHERE {$this->id} = ".$_POST['delete'];
			$this->db->query($query);
			$this->redirect();
		}
		//query the table
		if (empty($_GET['delete'])) {$this->redirect();}
		$post = $this->getRow($_GET['delete']);
		
		//truncate the post
		include $this->view_dir.'/delete.php';	
	}
	
	/** 
	 * get row from current table by passing id
	 * @param Int
	 * @return Array
	 */
	protected function getRow($id)
	{
	    $query = "SELECT * FROM {$this->table} WHERE {$this->id} = :id";
		$result = $this->db->prepare($query);
		$result->bindParam(':id', $id);
		$result->execute();
		
		$post = false;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$post = $row;			
		}	
		if (!$post) {
			$this->redirect();
		}
		return $post;
	}
	
	//------------------------------------------------------
	//               Post Methods
	//------------------------------------------------------
	
	
	/** 
	 * posts the New form to the database
	 * @param [Array]
	 * @return 
	 */
	protected function postInsert($meta=null)
	{
		if ($meta!=null) {
			$post = $_POST;
			$count = count($meta);
			
			//get the column string and the bind keys
			for($i=0; $i < $count; $i++) { 
				//ignore primary key
				if ($meta[$i]['Key'] != 'PRI') {
					$columnArray[] = $meta[$i]['Field'];
					$bindKeys[] = ':'.$meta[$i]['Field'];
				}
			}
			$bindKeys = implode(", ", $bindKeys);
			$columnString = implode(", ", $columnArray);
			
			//write the query
			$query = "INSERT INTO {$this->table}\n ($columnString)\n VALUES\n ($bindKeys)";
			$result = $this->db->prepare($query);
			
			//now bind the form to the bind keys
			for($i=0; $i < $count; $i++) { 
				foreach($post as $key => $value) {
					if ($key == $meta[$i]['Field']) {
						$bind = ":$key";
						$result->bindValue($bind, $value);
					}			
				}
			}
			
			$result->execute();
			$result = null;			
		}
		else {
			//generic post insert here
		}
	}
	
	/** 
	 * Posts the form edit to the database
	 * @param Array
	 * @return null
	 */
	protected function postEdit(Array $meta)
	{
	    if ($meta!=null) {
			$post = $_POST;
			$count = count($meta);
			
			//get the column string and the bind keys
			for($i=0; $i < $count; $i++) { 
				//ignore primary key
				if ($meta[$i]['Key'] != 'PRI') {
					$columnArray[] = $meta[$i]['Field'];
					$bindKeys[] = ':'.$meta[$i]['Field'];
				}
			}
			
			//make the set expression
			$count = count($columnArray);
			$columnString = '';
			for($i=0; $i < $count; $i++) { 
				$columnString .= $columnArray[$i]. " = ". $bindKeys[$i];
				if ($i!=$count-1) {
					$columnString .= ", \n";
				}
			}
			
			//write the query
			$query = "UPDATE {$this->table}\n SET \n$columnString \n WHERE {$this->id} = ".$_POST['edit'];
						
			$result = $this->db->prepare($query);
			
			//now bind the form to the bind keys
			$count = count($meta);
			for($i=0; $i < $count; $i++) { 
				foreach($post as $key => $value) {
					if ($key == $meta[$i]['Field']) {
						$bind = ":$key";
						$result->bindValue($bind, $value);
					}			
				}
			}
			try { 
				$result->execute();
			}
			catch(Exception $e) { 
				error_log($e->getMessage());
			}
			
			$result = null;			
		}
		else {
			//write generic update query
		}
	}

	/** 
	 * Redirects back to base of scaffold
	 * @param [$string]
	 * @return null
	 */
	protected function redirect($redirect=null)
	{
		if (!$redirect) {
			$redirect = 'http://'.URL::$abso;
			header("Location: $redirect");
			exit;
		}
	}
	
	/** 
	 * Checks datatyped inserts, returns ['error'] false if it
	 * validates or returns array of errors for each column
	 * @param Array
	 * @return false || Array
	 */
	protected function checkInsert(Array $meta)
	{
		$valid['error'] = false;
		$sizeofmeta = count($meta);
		
		//validation loop
		for($i=0; $i < $sizeofmeta; $i++) { 
			$field = $meta[$i]['Field'];
		    $type = $meta[$i]['Type'];
		    $null = $meta[$i]['Null'];
		    $key = $meta[$i]['Key'];
		    $default = $meta[$i]['Default'];
		    $extra = $meta[$i]['Extra'];
			
			//skip primary field
			if ($key == 'PRI') {
				continue;
			}
			if (strstr($type, "int")) {
				if ($_POST[$field] != '0' && !is_numeric($_POST[$field])) {
					$valid['error'][$field] = "This field must be a whole number";
				}
			}
			if ($type == 'datetime') {
				if (!strtotime($_POST[$field])) {
					$valid['error'][$field] = "This is not a valid date";
				}
			}
			if ($null == 'NO' AND empty($_POST[$field]) AND $_POST[$field] !=='0') {
				$valid['error'][$field] = "This field can not be empty";
			}			
		}
	    return $valid;
	}
	
	/** 
	 * gets the the table meta info
	 * method will only work with databases that support datatypes
	 * Currently only mysql in pdo
	 * @param 
	 * @return Array
	 */
	protected function getColumnMeta()
	{
	    $query = "SHOW COLUMNS FROM ".$this->table;
		try 
		{
			$result = $this->db->query($query);
			return $result->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(Exception $e) { 
			echo "Scaffolding Table: '{$this->table}'  not found";
			exit;
		}	
	}
	
	/** 
	 * Makes the column names pretty
	 * @param String
	 * @return String
	 */
	protected function cleanColumnNames($column)
	{
		$column = ucwords(str_replace('_', ' ', $column));
	    return $column;
	}
	
	/** 
	 * The list of supported database types
	 * @param 
	 * @return 
	 */
	protected function supportsDatatypes()
	{
		//returns the getColumnMeta Array for all methods if the db is supported
	    if (Database::$type == 'mysql' OR Database::$type == 'mysqli') {
			return $meta = $this->getColumnMeta();
					
		}
		//otherwise create a default type without form checking or custom formatting
		else {
			return false;
			echo "just get the column names with select statement";
		}
	}
	
}


?>