<?php
namespace Plash;

include dirname(__FILE__).'/crudhelper.php';
include dirname(__FILE__).'/pagination.php';
//------------------------------------------------------
//               Crud Class
//------------------------------------------------------

class Crud extends CrudHelper
{
	//public $outputFilter;

	/** 
	 * Crud Create Method
	 * @param [Array POST]
	 * @return INT lastInsertId || False
	 */

	public function create($post=null)
	{
		//set default input to POST data
		if ($post==null) {
			$post = $_POST;
		}
		
		//check reffering domain matches current domain
		$form_inputs = $this->getPostData($post);
		
		//check and get the input filter
		//if (method_exists($this, 'inputFilter')) {
		//	$form_inputs = $this->inputFilter($form_inputs);
		//}
				
		//make sure a table is set
		if (empty($this->sql['tables'])) {
			throw new Exception("Please Set at least one table");
		}
		
		//bind the column names for the INTO expression
		$values = $this->getBoundColumnNames($form_inputs);
		
		//write the query
	    $query = "INSERT \nINTO \n \t {$this->sql['tables']}\n\t ({$this->sql['columns']})\n VALUES\t ($values)";
	
		//prepare
		$stmt = $this->db->prepare($query);
		
		//bind params
	    $this->PDObindColumnParams($stmt, $form_inputs);
		
		//execute query
		$stmt->execute();
		$stmt = null;
		
		//return last insert id or false if no insert was made
		if ($this->db->lastInsertId()) {
			return $form_inputs;
			//return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	
	
	/** 
	 *  Crud Read
	 * @param [$limit INT]
	 * @param [$offset INT]
	 * @return Array || False
	 */
	
	public function read($limit=null, $offset=null)
	{
		if (empty($this->sql['tables'])) {
			throw new Exception("Please Set at least one table");
		}
		
		//if the select param is not set, set columns to all
		if (empty($this->sql['columns'])) {
			$this->sql['columns'] = "*";
		}
		
		//assign the limit and offset params
		if ($limit!='row') {
			$this->limit($limit, $offset);
		}
		
		//make the select query
		$query = "SELECT \n\t{$this->sql['columns']} \nFROM \n\t{$this->sql['tables']} \n";
		
		//make the innerjoin queries
		if (isset($this->sql['jointable'])) {
			$count = count($this->sql['jointable']);
			
			//throw an expection if number of join ons and join tables do not match
			if ($count != count($this->sql['innerjoin'])) {
				throw new Exception("make sure the number of join tables matches the number of join on params");
			}
			
			//form the join on queries
			for($i=0; $i < $count; $i++) { 
				$query .= "INNER JOIN \n\t{$this->sql['jointable'][$i]} \n";
				$query .= "ON \n\t{$this->sql['innerjoin'][$i]} \n";
			}
		}
			
		//make the where query
		if (isset($this->sql['where'])) {
			$query .= "WHERE \n\t{$this->sql['where']} \n";
		}	
		//make the and queries
		if (isset($this->sql['and'])) {
			$count = count($this->sql['and']);
			for($i=0; $i < $count; $i++) { 
				$query .= "AND \n\t{$this->sql['and'][$i]} \n";
			}
		}
		if (isset($this->sql['orderby'])) {
			$query .= "ORDER BY \n\t{$this->sql['orderby']} \n";
		}
		if (isset($this->sql['limit'])) {
			$query .= "LIMIT \n\t{$this->sql['limit']} \n";
		}
		
		//query the db
		$result = $this->queryDB($query);
		
		//return rows into array
		$post = array();
		
		//see if class is available or not found set to generic base model class
		//$class = class_exists($this->class) ? $this->class : 'Model';
		
		//fetch hash array
		if ($limit=='row') {
			$post = $result->fetch(PDO::FETCH_ASSOC);
			//$post = $result->fetchAll(PDO::FETCH_CLASS, $class);
			//if (isset($post[0])) {
			//	$post = $post[0];
			//}
		}
		//fetch numbered array
		else {
			//$post = $result->fetchAll(PDO::FETCH_CLASS, $class);
			$post = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		
		//check output filter
		//if (isset($post) AND method_exists($this, 'outputFilter')) {
		//	$post = $this->outputFilter($post, $this->outputFilter);
		//}
		
		//return data
		if (isset($post)) {
			return $post;
		}
		else {
			return false;
		}
		
	}
	
	
	/** 
	 * Crud Update Method
	 * @param [Array POST]
	 * @return Boolean
	 */
	
	public function update($post=null)
	{
		//set default input to POST data
		if ($post==null) {
			$post = $_POST;
		}
		//SET CREATE AND UPDATE TO USE SAME BINDS ARRAY  -!!!!!!!!!
		//FOR COLUMNS AS IS USED ON READ --!!!!!!!!!!!!!
		//finish update method and then do delete
		
		//check reffering domain matches current domain
		$form_inputs = $this->getPostData($post);
		
		
		//check and get the input filter
		//if (method_exists($this, 'inputFilter')) {
		//	$form_inputs = $this->inputFilter($form_inputs);
		//}
		
		
		//make sure a table is set
		if (empty($this->sql['tables'])) {
			throw new Exception("Please Set at least one table");
		}
		
		//create a the array of binds		
		$column_binds = $this->createBindArray($form_inputs);
				
		
		////write the first part of query
	    $query = " UPDATE\n\t{$this->sql['tables']}\n SET\n\t";
		
		//format SET statement
		$bindcount = count($column_binds);
		for($i=0; $i < $bindcount; $i++) { 
			$query .= "{$column_binds[$i]['column']} = {$column_binds[$i]['key']}";
			if ($i != $bindcount-1) { $query .= ", \n\t";}
		}
		
		//make the optional where query
		if (isset($this->sql['where'])) {
			$query .= "\nWHERE \n\t{$this->sql['where']} \n";
		}
	
		//make the optional and query
	    if (isset($this->sql['and'])) {
	    	$count = count($this->sql['and']);
	    	for($i=0; $i < $count; $i++) { 
	    		$query .= "AND \n\t{$this->sql['and'][$i]} \n";
	    	}
	    }
		//prepare
		$stmt = $this->db->prepare($query);
		
		//bind column params
		$this->PDObindColumnParams($stmt, $form_inputs);
		
		//bind and and, where params
		$count = count($this->binds);
	    for($i=0; $i < $count; $i++) { 
	    	$stmt->bindParam(":{$this->binds[$i]['name']}", $this->binds[$i]['value'], PDO::PARAM_STR);
	    }
	
		//execute
		if (!$this->debug) {
			if ($stmt->execute()) {
				$stmt = null;
				return true;
			}
			else {
				$stmt = null;
				return false;
			}
		} else {
			 echo "<hr><strong>sql</strong><pre>$query</pre><hr>"; 
		}
			
	}
	
	
	
	/** 
	 * Crud Delete Method
	 */
	
	public function delete()
	{
	    if (empty($this->sql['tables'])) {
			throw new Exception("Please Set at least one table");
		}
		
		if (!isset($this->sql['where'])) {
			throw new Exception("Please set a where query for this delete statement");
		}
		
		$query =  
		"
		DELETE 
		  FROM 
			{$this->sql['tables']}
		 WHERE 
			{$this->sql['where']}
		";
		
		//append optional AND param
		if (isset($this->sql['and'])) {
	    	$count = count($this->sql['and']);
	    	for($i=0; $i < $count; $i++) { 
	    		$query .= "AND \n\t{$this->sql['and'][$i]} \n";
	    	}
	    }
		$stmt = $this->db->prepare($query);
		
		//bind params
		$bindcount = count($this->binds);
		for($i=0; $i < $bindcount; $i++) { 
			//maybe change to bindValue
			$stmt->bindParam(":{$this->binds[$i]['name']}", $this->binds[$i]['value'], PDO::PARAM_STR);
		}
		
		//execute the statement
		$stmt->execute();
		$stmt = null;	
	}
	
	
	/** 
	 * Crud Count Rows
	 * @param [STRING id]
	 * @return INT
	 */
	public function count($id='id')
	{
		$this->sql['columns'] = 'COUNT('.$id.') AS count';
		
		//$output_filter = $this->outputFilter;
		//$this->outputFilter = 'off';
	    
		$count = $this->read('row');
		
		//$this->outputFilter = $output_filter;
		$this->count = $count['count'];
		return $this->count;
	}
	
	/** 
	 * Pass SQL directly into Crud
	 */
	public function sql($query) { $this->query($query); }
	
	public function query($query)
	{
		try 
		{
			//prepare the query
			$this->flush();
			$stmt = $this->db->prepare($query);

			$stmt->execute();
			
			//execute	
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}
		catch(Exception $e) { 
			error_log($e->__toString());
		}
	}
}






?>