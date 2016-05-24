<?php
namespace Plash;

class DateHelper
{
	
	public static function format($date, $format='')
	{
		//just quit now if there is no date
		if (!$date) {
			return;
		}
		
		switch ($format) {
			case "": //Fri, Mar 06th 2009 at 08:24:39 am
				$date = date('D, M dS Y \\a\\t h:i:s a', strtotime($date));
			break;
			
			case 1: //Friday, March 06th 2009 at 08:24am
				$date = date('l, F jS Y \\a\\t g:ia', strtotime($date));
			break;
			
			case 2: //Fri, Mar 06th 2009
				$date = date('D, M dS Y', strtotime($date));
			break;
			
			case 3: //Friday, Mar 06th 2009
				$date = date('l, M dS Y', strtotime($date));
			break;
			
			case 4: // m/d/y
				$date = date('n/j/y', strtotime($date));
			break;
			
			case 5: // y/m/d
				$date = date('y/n/j', strtotime($date));
			break;
			
			case 6: // y/m/d/h/m/s
				$date = date('Y/m/d/G/i/s', strtotime($date));
			break;
		
			case 7: // y/m/d/h/m/s
				$date = date('y/n/j/G/i/s', strtotime($date));
			break;
			
			case 'adt': // y/m/d/h/m/s
				$date = date('n/j/y G:i:s', strtotime($date));
			break;
			
			case 'r': //Fri, 06 Mar 2009 08:24:39 -0800
				$date = date('r', strtotime($date));
			break;
			
			case 'dt': // 
				$date = date('Y-n-j G:i:s', strtotime($date));
			break;
			
			default:
				$date = 'this is not a valid date format';
			
		}
		return $date;
	}
}

?>