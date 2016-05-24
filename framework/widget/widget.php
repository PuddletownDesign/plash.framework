<?php

class Widget
{
	private $widget;
	
	/** 
	 * Creates and returns a widget object
	 */
	public function __construct($widget) 
	{
		$this->loadWidget($widget);
	}
	
	/** 
	 * Includes the widget file
	 */
	public function loadWidget($widget)
	{
	    
	}
}


?>