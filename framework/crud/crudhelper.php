<?php

//------------------------------------------------------
//               Crud Interface
//------------------------------------------------------
/** 
 * 
 * sql builder CLass
 * Crud Helper functions
 */

abstract class CrudHelper
{
	public $debug = false, $table, $class, $sql = array();
	
	protected $db, $binds = array();
	
	
	public function __construct() 
	{
		//get a db instance
		$this->setDB();
		
		//set any table args if present
		$table = func_get_args();
		$table = implode($table, ', ');
		$this->sql['tables'] = $table;
		$this->table = $table;
		$this->class = ucwords($table);
	}
	
	//------------------------------------------------------
	//               set up
	//------------------------------------------------------
	
	protected function setDB()
	{
		if (!isset($this->db)) {
			$this->db = Database::getInstance();
		} 
	}
	
	public function flush()
	{
		unset($this->sql);
		unset($this->binds);	
	}
	
	public function debug($bool=true)
	{
	    $this->debug = $bool;
		return $this;
	}
	
	//public function filter($type='post')
	//{
	//	if ($type!='off') {
	//		$this->outputFilter = $type;
	//	}
	//	return $this;
	//}
	//------------------------------------------------------
	//               columns and tables
	// see about grouping these functions into a single overloaded method
	//------------------------------------------------------
	
	public function columns()
	{
		//set the column args
		$select = func_get_args();
		$select = implode($select, ', ');
		$this->sql['columns'] = $select;
		return $this;
	}
	
	public function table()
	{
	    $from = func_get_args();
		$from = implode($from, ', ');
		$this->sql['tables'] = $from;
		$this->table = $this->sql['tables'];
		return $this;
	}
	
	//------------------------------------------------------
	//               statements
	//------------------------------------------------------
	
	
	public function where($where, $datatype=null)
	{
		if ($datatype) {
			$where = $this->setBind($where, $datatype, 'where');
		}
		$this->sql['where'] = $where;
		return $this;
	}
	
	public function and_($and, $datatype=null)
	{
		//if the datatype is not null then bind the params
	    if ($datatype) {
			//count the array length
			$count = "";
			if (isset($this->sql['and'])) {
				$count = count($this->sql['and']);
			}
			//set binds and append length to and id
			$and = $this->setBind($and, $datatype, 'andparam'.$count);
		}
		$this->sql['and'][] = $and;
		return $this;
	}
	
	public function jointable($jointable)
	{
		$this->sql['jointable'][] = $jointable;
		return $this;
	}
	
	public function innerjoin($ij, $datatype=null)
	{
		//if the datatype is not null then bind the params
	    if ($datatype) {
			//count the array length
			$count = "";
			if (isset($this->sql['innerjoin'])) {
				$count = count($this->sql['innerjoin']);
			}
			//set binds and append length to and id
			$ij = $this->setBind($ij, $datatype, 'innerjoin'.$count);
		}
		$this->sql['innerjoin'][] = $ij;
	    return $this;
	}
	
	public function orderby($ob=null)
	{
	    $this->sql['orderby'] = $ob;
		return $this;
	}
	
	public function limit($limit=null, $offset=null)
	{
	    $this->sql['limit'] = $limit;
		
		if ($offset != null) {
			$this->sql['limit'] = "$offset, $limit";
		}
		return $this;
	}
	
	public function queryDB($query)
	{
	    //check to see if debug is set to true
		if ($this->debug) {
			 echo "<hr><strong>sql</strong><pre>$query</pre><hr>"; 
		} else {
			try 
			{
				//prepare the query
				$result = $this->db->prepare($query);

				//bind the parameters
				if (isset($this->binds)) {
					$result = $this->PDObindParams($result);
				}

				//execute		
				$result->execute();
			}
			catch(Exception $e) { 
				error_log($e->__toString());
			}
			return $result;
		}
		
	}
	
	//------------------------------------------------------
	//              statement prebound alias'
	//------------------------------------------------------
	public function _innerjoin($ij, $datatype='s')
	{
		if ($datatype===null){ $datatype='s'; }
		$this->innerjoin($ij, $datatype);
	    return $this;
	}
	public function _where($where, $datatype='s')
	{
		if ($datatype===null){ $datatype='s'; }
	    $this->where($where, $datatype);
		return $this;
	}
	public function _and_($and, $datatype='s')
	{
		//call the and_ method with dt set as string to force bind
		if ($datatype===null){ $datatype='s'; }
	    $this->and_($and, $datatype);
		return $this;
	}
	
	//------------------------------------------------------
	//               Get POST data
	//------------------------------------------------------
	protected function getPostData($post)
	{
		//only accept post data that is specified in the coloumns param
		$columnsArray = explode(', ', $this->sql['columns']);
		$returnPost = array();
		$columnCount = count($columnsArray);
		
		for($i=0; $i < $columnCount; $i++) { 
			foreach($post as $key => $value) {
				if ($key == $columnsArray[$i]) {
					$returnPost[$key] = $value;
				}
			}
		}
		return $returnPost;
	}
	
	
	//------------------------------------------------------
	//               binding
	//------------------------------------------------------
	protected function createBindArray($form_inputs)
	{
		foreach($form_inputs as $key => $value) {
			$form_binds[] = array('column'=> "$key", 'key'=> ":$key", 'value' => $value);
		}
		return $form_binds;
	}
	
	protected function getBoundColumnNames($post)
	{
		$this->sql['columns'] = '';
	    foreach($post as $key => $value) {
	    	$colarr[] = ":$key";
			$this->sql['columns'][] = $key;
	    }
		$this->sql['columns'] = implode(', ', $this->sql['columns']);
		$colstr = implode(', ', $colarr);
		return $colstr;
	}
	
	protected function PDObindColumnParams($stmt, $form_inputs)
	{
		foreach($form_inputs as $key => $value) {
	     	$binds[] = array('column' => ":$key", 'value' => $value);
	    }
		// bindParam will not work inside a foreach loop :| idk wtf
		$bindsize = count($binds);
		for($i=0; $i < $bindsize; $i++) { 
			$stmt->bindParam($binds[$i]['column'], $binds[$i]['value']);
		}
	}
	
	protected function setBind($param, $dt='s', $name)
	{	
		$param_operator = $this->equivalenceOperators($param);
		$param_array = explode($param_operator, $param);
		$param_array = array_map('trim', $param_array);
		if (count($param_array)!=2) {
			throw new Exception("there was a problem binding the $name statement");
		}
		
		//set all parameters to be bound to the binds array for later use
	    $this->binds[] = array('name' => $name, 'datatype' => $dt, 'value' => $param_array[1]);
		
		//replace value in query string with binded key
		$param = $param_array[0]." $param_operator :$name";
		return $param;
	}
	
	protected function PDObindParams(PDOStatement $result)
	{		

	    $count = count($this->binds);
		for($i=0; $i < $count; $i++) { 
			switch ($this->binds[$i]['datatype']) {
				case 's':
					$dt = PDO::PARAM_STR;
				break;
				case 'i':
					$dt = PDO::PARAM_INT;
				break;
				case 'b':
					$dt = PDO::PARAM_BOOL;				
				break;
				default:
					$dt = PDO::PARAM_STR;
					
			}
			$result->bindParam($this->binds[$i]['name'], $this->binds[$i]['value'], $dt);
		}
		return $result;
	}
	
	protected function equivalenceOperators($expression)
	{
		//set an array of operators to be used with prepared queries only
		//set most speicific to least specific
		$operators = array('!=', '>=', '<=', '>', '<', '=', 'LIKE');
		$o_count = count($operators);
		for($i=0; $i < $o_count; $i++) { 
			if (strstr($expression, $operators[$i])){  
				$used_op = $operators[$i];
				break;
			}
		}
	    return $used_op;
	}
	
	
}


?>