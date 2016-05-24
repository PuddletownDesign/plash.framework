<?php
/*/ 
crud paging usage

//------------------------------------------------------
//               Paging a List
//------------------------------------------------------

Do not use the crud class to read the query, instead, create a new paging class
and pass the crud class to the constructor, after all params have been set.

in the read method pass the number of posts to appear on a page

-------------------- Usage -------------------------------

$crud = new Portfolio('table', [$url='page/', $basepage=null]);
$crud->outputFilter = 'list';
$crud
	->columns("id, title, text, completed")
;

$pagination = new Pagination($crud);
$post = $pagination->read(10);

----------------------------------------------------------

output is array with $array['posts'][$i] and array['paging']

paging array values are:
url - the paging url passed in the constructor, default is 'page/'
total - the total number of posts that match the query
remaining - the remaining number of posts after this page
current - the current page number
last - the total number of pages 
next - the next seqential page in the list (will only be set if next page is available)
prev - the previous seqential page in the list (will only be set if next page is available)



//------------------------------------------------------
//               Paging a row
//------------------------------------------------------

set columns to be shown for next and prev rows
optionally set additional AND parameters to refine paging options

-------------------- Usage ------------------------------

$crud = new Portfolio('portfolio');
$crud->outputFilter = 'list';

$crud
	->debug(true)
	->columns("portfolio.title, portfolio.price, portfolio.url, portfolio_categories.title AS cat_title", "sections.title AS sec_title")
;
$pagination = new Pagination($crud);
$pagination
	->debug(true)
	->columns("title, url, id, cat_id, completed")
	->and_("portfolio.cat_id = 2")
;	
$post = $pagination->read('row', 'completed');

----------------------------------------------------------

output is $array['post'] and $array['paging']['prev'] $array['paging']['next']

post contains crud query data

prev and next arrays contain column data set in columns param

*/



class Pagination
{
	
	protected $crud, $debug = false;
	protected $paging_path, $paging_url, $lastpage, $remaining;
	
	public $paging_crud_class = 'Crud';
	
	/** 
	 * Pagination Constructor
	 * @param Crud Object, [STRING, STRING]
	 */
	
	public function __construct(Crud &$crud, $paging_url="page/") 
	{
		$this->crud = $crud;
		
		//if the base page param is not set then set 
		//the base of the paging to the site root
		
		//set up paging
		$paging_url_array = explode("/", $paging_url);
		$url_name = $paging_url_array[0];
		
		//subtract one form the count to compensate for explode fragment
		$count = count($paging_url_array)-1;
		$path = "";
		for($i=0; $i < $count; $i++) { 
			$path .= "../";
		}
		$this->getPageNumber($url_name, $path);
		
		$this->paging_path = $path;
		$this->paging_url = $paging_url;
	}
	
	
	/** 
	 * Crud Paging Read Interaface
	 * factory, read row/ read list
	 * INT for list of posts
	 * 'row' for a single post with next/prev
	 *
	 * @param [INT/STRING, INT]
	 * @return Array
	 */
	
	public function read($limit=100, $id='id')
	{
		if ($limit === 'row') {
			$return = $this->readRow($id);
		}
		else {		
			$return = $this->readList($limit);
		}
		return $return;
	}



	//------------------------------------------------------
	//               protected methods
	//------------------------------------------------------
	protected function readRow($id)
	{
	    $post['post'] = $this->crud->read('row');
		
		if (!$post['post']) {
			return false;
		}
		
		//check to make sure the id we are going to compare is set
		if (!isset($post['post'][$id])) {
			throw new Exception("Select the id column in the 
			initial query that is assigned as the section 
			parameter to the pagination->read('row', 'id').\n 
			Id is the default for the pagination read row");
		}
		
		//set up crud class to get next and prev
		$crud = new $this->paging_crud_class ($this->crud->tables);
		if (isset($this->sql['columns'])) {
			$crud->columns($this->sql['columns']);
		}
		if (isset($this->sql['and'])) {
			$and_count = count($this->sql['and']);
			for($i=0; $i < $and_count; $i++) { 
				$crud->and_($this->sql['and'][$i]);
			}
		}
		
		//debug option
		if ($this->debug) { $crud->debug(true); }
		
		//get next row and check if there was a return
		$prev = $crud->_where("$id < {$post['post'][$id]}")
					 ->orderby("$id DESC")
					 ->read('row')
		; if ($prev) { $post['paging']['prev'] = $prev;}
		
		//get previous row and check if there was a return
		$next = $crud->_where("$id > {$post['post'][$id]}")
					 ->orderby("$id ASC")
					 ->read('row')
		; if ($next) { $post['paging']['next'] = $next; }
		
		return $post;
	}
	
	/**  
	 * Crud Paging Read List
	 * @param INT
	 * @return Array
	 */
	protected function readList($limit)
	{
		
	    $offset = $this->pagenumber -1;
		$pagingoffset = $offset * $limit;
		
		//set limit
		$this->limit = $limit;
		$post['posts'] = $this->crud->read($limit, $pagingoffset);
		

		
		
		//total rows
		$column = explode(', ', $this->crud->sql['columns']);	
		$this->count = $this->crud
			->limit()
			->count($column[0])
		;	
		
		
		//last page
		$this->lastpage = ceil($this->count/$this->limit);
		
		//remaining posts
		$this->remaining = $this->count - ($offset*$this->limit);
		
		//check if current page is greater then last page 
		//or the page is set in url as 1 or 0
		//REDIRECT BACK TO PAGE 1
		if ($this->pagenumber > $this->lastpage || $this->pagenumber == 0) {
			if ($this->count != 0) {
				$redirect = $this->paging_path;
				header("Location: $redirect", TRUE, 404);
				exit();
			}
		}
		
		//Initialize return array
		$post['paging']['prev'] = false;
		$post['paging']['next'] = false;
		$post['paging']['list_offset'] = 0;
		$post['paging']['url'] = "";
		
		//offset the list if page is higher then one
		if ($this->pagenumber > 1) {
			$post['paging']['list_offset'] = $offset*$this->limit +1;
			$post['paging']['path'] = $this->paging_path;
		}
		else {
			$post['paging']['url'] = $this->paging_url;
		}
		
		//assign to output array
		$post['paging']['total'] = $this->count;
		$post['paging']['remaining'] = $this->remaining;
		$post['paging']['current'] = $this->pagenumber;
		$post['paging']['last'] = $this->lastpage;
		
		//get previous page
		if ($this->pagenumber > 1) {
			if ($this->pagenumber == 2) {
				$post['paging']['prev'] = $this->paging_path;
			}
			else {
				$post['paging']['prev'] = $this->pagenumber - 1;
			}
		}
		
		//check for next page
		if ($this->remaining > $limit) {
			$post['paging']['next'] = $this->pagenumber + 1;
			//get the last page
		}
		
		return $post;
	}

	//------------------------------------------------------
	//               Helper functions
	//------------------------------------------------------
	public function columns($columns)
	{
	    $this->sql['columns'] = $columns;
		return $this;
	}
	public function where($where)
	{
	    $this->sql['where'] = $where;
		return $this;
	}
	public function and_($and)
	{
		$this->sql['and'][] = $and;
	    return $this;
	}
	public function debug($debug=true)
	{
	    $this->debug = $debug;
		return $this;
	}

	private function getPageNumber($url_name, $path)
	{
	   if (Url::segment(2)== $url_name) {
			$pagenumber = Url::segment(3);
			
			//if the page number is empty or not an int
			//redirect back to the sec
			if (!Url::segment(3)) {
				header("Location: $path");
				exit;
			}
			
		} else { $pagenumber = 1; }
		$this->pagenumber = $pagenumber;
	}	
}


?>