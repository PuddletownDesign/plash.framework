<?php
include dirname(__FILE__).'/markdown.php';
include dirname(__FILE__).'/smartypants.php';

class Typography
{
	/** 
	 * Markdown Interface
	 */
	public static function markdown($text, $header_level=2, $allowHTML=false, $link_rel='no-follow', $useSmarty=true)
	{
	    $markdown = new Markdown($header_level, $allowHTML, $link_rel, $useSmarty);
		return $markdown->transform($text);
	}
	
	public static function smartypants($text)
	{
	    $smartypants = new SmartyPants();
		return $smartypants->transform($text);
	}
}


?>