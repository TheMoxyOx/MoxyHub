<?php

/**
 * Handle formatting of stuff for user output
 * $Id$
 */

/* set up the defines for the enum of request types we allow */

class Format
{
	// define some constants
	const USEOPTION = 1;

	public static function raw($text)
	{
		// use carefully :)
		return $text;
	}
	
	
	public static function for_textarea($text)
	{
		// wtf are those slashes doing. 
		$text = stripslashes($text);

		// first, turn things into entities
		$text = htmlentities($text, ENT_COMPAT, 'UTF-8', FALSE);
		
		return $text;
	}
	
	/**
	 * Format a block of text nicely. this includes linking things, nl2brs, escaping weird characters, etc etc.
	 *
	 * @return void
	 **/
	public static function blocktext($text)
	{
		// wtf are those slashes doing. 
		$text = stripslashes($text);

		// first, turn things into entities
		$text = htmlentities($text, ENT_COMPAT, 'UTF-8', FALSE);

		// now do some markups.
		$text = Format::linkify($text);
		
		// finally, through in some br's
		$text = nl2br($text);
		return $text;
	}
	
	public static function text($text)
	{
		// wtf are those slashes doing. 
		$text = stripslashes($text);

		// first, turn things into entities
		$text = htmlentities($text, ENT_COMPAT, 'UTF-8', FALSE);

		return $text;
	}
	
	public static function filename_safe($str)
	{
		// \W === _not_ letters, digits, and underscores.
		return preg_replace('/\W+/', '_', $str);
	}
	
	public static function val($text)
	{
		return htmlentities($text, ENT_COMPAT, 'UTF-8', FALSE);
	}
	
	// break str at char limit, stepped back until you find any of $break_chars. 
	// $break_char is also removed.
	// append trailer on the end, 
	public static function trunc($str, $char_limit, $trailer = '...', $break_chars = ',. ')
	{
		if (strlen($str) <= $char_limit)
		{
			return $str;
		}
		
		$str = substr($str, 0, $char_limit);
		
		$ends = array();
		for($i = 0; $i < strlen($break_chars); $i++)
		{
			$break_char = substr($break_chars, $i, 1);
			$occurence = strripos($str, $break_char);
			if ($occurence !== FALSE)
			{
				$ends[] = $occurence;
			}
		}
		
		if (count($ends) > 0)
		{
			$new_end = max($ends);
			$str = substr($str, 0, $new_end);
			// also, get rid of subsequent trim characters
			$str = rtrim($str, $break_chars);
		}
		
		return $str . $trailer;
	}
	
	/**
	 * Convert from a 'bytes' suffix to a number
	 * @param   $val    string  The value to convert from
	 * @param           int     The final value
	 */
	public static function convert_to_bytes($val) 
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val) - 1});
		switch($last) {
			case 'g' :
				$val *= 1024; // fallthrough
			case 'm' :
				$val *= 1024; // fallthrough
			case 'k' :
				$val *= 1024; // fallthrough
		}
		return $val ;
	}

	
	
	/**
	 * Try to make html links into proper links
	 *
	 * @return the linked text
	 **/
	public static function linkify($text)
	{
		// try to replace all of 
		$protocols = '(?P<protocol>http|https|ftp|ssh)';
		$reg_url = '(' . $protocols . ':\/\/)?(?P<url>(www\.)?(?:[A-Z0-9-]+\.)+[A-Z]{2,4}[A-Z0-9\/?&;.=_-]*)';
		
		// - <a href='www.foo.com'>www.foo.com</a>
		// no change, ie $text = $text
		
		// - www.foo.com
		// adapted form the ever usefull regular-expresions.info: 
		$text = preg_replace_callback("/(?P<leader>[$\s])$reg_url\b/i", 
																	array('Format', 'linkify_callback'),
																	$text);
		return $text;
	}
	
	public static function linkify_callback($matched_elements)
	{
		// it appears copper servers don't know how to do named parameters, so we are back to indexes. Leave this here for whenever it gets fixed.
		// $protocol = ($matched_elements['protocol'] == '') ? 'http' : $matched_elements['protocol'];
		// $url = $protocol . '://' . $matched_elements['url'];
		// return $matched_elements['leader'] . '<a href="' . $url . '">' . $url . '</a>';
		$protocol = ($matched_elements[3] == '') ? 'http' : $matched_elements[3];
		$url = $protocol . '://' . $matched_elements[4];
		return $matched_elements[1] . '<a href="' . $url . '">' . $url . '</a>';
	}
	
	/**
	 * Print a date, including i18n of months.
	 * @param   $date				string  The date to format, in YYYY-MM-DD format.
	 * @param   $nice_dates	string  Allow dates to be 'nicified', ie today, tomorrow, etc.
	 * @param   $alternate	string  So at some point, there were two date functions in the code base. I don't know why. The alternate parameter
	 * 															preserves the old functionality until it can be completely weeded out.
	 * @return					  The formatted date string.
	 */
	public static function date($date, $nice_dates = TRUE, $standard = TRUE) 
	{
		// first, ensure that the date is actually just a date, and not a datetime.
		list($date, $junk) = array_pad(explode(' ', $date, 2), 2, null);
		
		if ($standard)
		{
			$formats = array('1' => 'd M y', '2' => 'd M Y', '3' => 'd-m-Y', '4' => 'm-d-Y', '5' => 'Y-m-d', '6' => 'Y-d-m');
			// $dateFormat is actually a number, indexing the above array. I don't know why (she swallowed a fly).
			$dateFormat = Settings::get('PrettyDateFormat'); 
			
		} else {
			// Use the old formatting stuff, it is the old way
			$formats = array('1' => 'Y-m-d', '2' => 'Y-d-m', '3' => 'd-m-Y', '4' => 'm-d-Y');
			$dateFormat = Settings::get('DateFormat'); 
		}
		
		$monthNames = array(
			MSG_JANUARY_SHORT, 
			MSG_FEBRUARY_SHORT, 
			MSG_MARCH_SHORT, 
			MSG_APRIL_SHORT, 
			MSG_MAY_SHORT, 
			MSG_JUNE_SHORT, 
			MSG_JULY_SHORT, 
			MSG_AUGUST_SHORT,
			MSG_SEPTEMBER_SHORT, 
			MSG_OCTOBER_SHORT, 
			MSG_NOVEMBER_SHORT, 
			MSG_DECEMBER_SHORT
		);
		
		if (strlen($date) > 0 && strtotime($date) > 0)
		{
			if ( ($nice_dates == TRUE) && ( ! Settings::get('DisableNiceDates') ) )
			{
				// get the difference in times in seconds.
				$diff = time() - strtotime($date);
				
				// now get the difference in days
				$day_diff = floor($diff / 60 / 60 / 24);

				switch($day_diff)
				{
					case 0: return MSG_TODAY;

					// future
					case 1: return MSG_YESTERDAY;
					case 2: 
					case 3:
					case 4: return $day_diff . " days ago";

					// past
					case -1; return 'Tomorrow';
					case -2: 
					case -3:
					case -4: return 'In ' . (-$day_diff) . " days";
					// no relative dates, then fall through and do normal date presentation.
				}

			}
			
			list($y, $m, $d) = explode("-", $date);
			$date = date($formats[$dateFormat], mktime(0, 0, 0, $m, $d, $y));

			// Cater for different languages.
			// This searches through for what PHP has given for the month name, and localises it, but only in the first two options
			// as they have capital M, indicating short month.
			if (($standard) && ($dateFormat < 3))
			{
				$shortName = date('M', mktime(0, 0, 0, $m, $d, $y));
				$actualName = $monthNames[$m-1];
				$date = str_replace($shortName, $actualName, $date);
			}
		}
		else {
			$date = "--";
		}

		return $date;
	}

	// be generally nice.
	public static function timeago($data, $is_timestamp = FALSE)
	{
		// taken from http://php.net/manual/en/function.time.php and tweaked.
		$periods		= array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths		= array("60","60","24","7","4.35","12","10");

		$now				= time();

		if ($is_timestamp)
		{
			$unix_date = $data;
		} else {
			$unix_date	= strtotime($data);
		}
		
		// check validity of date
		if(empty($unix_date)) {   
			return "&hellip";
		}

		// is it future date or past date
		if($now > $unix_date) 
		{
			$difference		= $now - $unix_date;
			$tense				= "ago";

		} else 
		{
			$difference		= $unix_date - $now;
			$tense				= "from now";
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
			$periods[$j].= "s";
		}

		if ($difference == 0)
		{
			return "Just then...";
		} else {
			return "$difference $periods[$j] {$tense}";
		}
	}	
	
	/**
	 * Print a date + time, including i18n of months.
	 * @param   $date	   string  The date to format, in variable formats
	 * @param   $dateFormat string  The desired end format.
	 * @return					  The formatted date string.
	 */
	public static function date_time($date, $dateFormat = NULL)
	{
		/*
			TODO This should use the above public static function for the month i18n
		*/
		// Ignore the 2nd param, it is the old way
		$formats = array('1' => 'd M y h:i A', '2' => 'd M Y h:i A', '3' => 'd-m-Y h:i A', '4' => 'm-d-Y h:i A', '5' => 'Y-m-d h:i A', '6' => 'Y-d-m h:i A');
		$dateFormat = Settings::get('PrettyDateFormat'); 
		$monthNames = array(MSG_JANUARY_SHORT, MSG_FEBRUARY_SHORT, MSG_MARCH_SHORT, MSG_APRIL_SHORT, 
			MSG_MAY_SHORT, MSG_JUNE_SHORT, MSG_JULY_SHORT, MSG_AUGUST_SHORT, 
			MSG_SEPTEMBER_SHORT, MSG_OCTOBER_SHORT, MSG_NOVEMBER_SHORT, MSG_DECEMBER_SHORT);

		if (strlen($date) > 0 && strtotime($date) > 0)
		{
			list($date, $time) = explode(' ', $date);
			list($y, $m, $d) = explode("-", $date);
			list($h, $min, $s) = explode(":", $time);
			$date = date($formats[$dateFormat], mktime($h, $min, $s, $m, $d, $y));

			// Cater for different languages.
			if ($dateFormat < 3)
			{
				$shortName = date('M', mktime($h, $min, $s, $m, $d, $y));
				$actualName = $monthNames[$m-1];
				$date = str_replace($shortName, $actualName, $date);
			}
		}
		else 
			$date = "--";

		return $date;
	}

	public static function parse_date($date)
	{
		// Current date formats in Copper are:
		$formats = array('1' => 'Y-m-d', '2' => 'Y-d-m', '3' => 'd-m-Y', '4' => 'm-d-Y');
		$dateFormat = Settings::get('DateFormat');

		list($date, $time) = explode(' ', $date); // Preserve time.
		$date = explode('-', $date);

		switch ($dateFormat)
		{
			case "1": $timestamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]); break;
			case "2": $timestamp = mktime(0, 0, 0, $date[2], $date[1], $date[0]); break;
			case "3": $timestamp = mktime(0, 0, 0, $date[1], $date[0], $date[2]); break;
			case "4": $timestamp = mktime(0, 0, 0, $date[0], $date[1], $date[2]); break;
		}

		$date = date('Y-m-d', $timestamp);
		return ($time) ? "$date $time" : $date;
	}

	/**
	 * Uniformly format some numbers
	 * @param   $number float   the number to format
	 * @return		  string  the formatted number
	 */
	public static function number($number)
	{
		$places = Settings::get('DecimalPlaces');
		$dp = Settings::get('DecimalPoint');
		$ts = Settings::get('ThousandsSeparator');
		return number_format((double) $number, $places, $dp, $ts);
	}

	/**
	 * Uniformly format some hours
	 * @param   $hours  float   the hours to format
	 * @return		  string  the formatted hours
	 */
	public static function hours($hours)
	{
		$places = Settings::get('DecimalPlaces');
		$dp = Settings::get('DecimalPoint');
		$ts = Settings::get('ThousandsSeparator');
		return number_format((double) $hours, $places, $dp, $ts);
	}

	/**
	 * Uniformly format some money
	 * @param   $amount float   the money to format
	 * @return		  string  the formatted money
	 */
	public static function money($amount, $convert_to_isodicks = FALSE)
	{
		$symbol = Settings::get('CurrencySymbol');
		$places = Settings::get('MoneyDecimalPlaces');
		$dp = Settings::get('MoneyDecimalPoint');
		$ts = Settings::get('MoneyThousandsSeparator');

		// this is used generally when outputting pdfs. We're trying to move to utf8, but the pdf library doesn't support it atm. 
		// so convert symbols to their iso equivalents.
		if ($convert_to_isodicks)
		{
			$symbol = iconv("UTF-8", "ISO-8859-1", $symbol);
		}
		
		return $symbol.number_format((double) $amount, $places, $dp, $ts);
	}

	public static function options($set, $current = null, $value_var = 'id', $text_var = 'value')
	{
		$str = '';
		foreach($set as $key => $option)
		{
			// Are our options an objecT? like an instance of the items class?
			if (is_object($option))
			{
				$selected = self::is_selected($option->$value_var, $current, $value_var);
				$str .= "<option value='" . $option->$value_var . "' $selected>" . $option->$text_var . "</option>" . PHP_EOL;
			} else if (is_array($option))
			{ // no, perhaps an array inside the array, in which case we need to extract just a certain key?
				$selected = self::is_selected($option[$value_var], $current);
				$str .= "<option value='" . $option[$value_var] . "' $selected>" . $option[$text_var] . "</option>" . PHP_EOL;
			} else {
				// okay, i think we are just iterating on simple things.
				// if this is set, we want to use the same thing as the value as what is the option itself.
				if ($value_var == Format::USEOPTION) 
				{
					$key = $option;
				}

				$selected = self::is_selected($key, $current);
				$str .= "<option value='" . $key . "' $selected>" . $option . "</option>" . PHP_EOL;
			}
		}

		return $str;
	}

	// key is what we are comparing with. Other key is the other comparison, which might be an array or a straight value
	private static function is_selected($key, $other_key, $value_var = 'id', $ret_if_true = 'selected="selected"', $ret_if_false = '')
	{
		// if it's an array, or perhaps an items extended class, we should try for that either way.
		if (is_array($other_key))
		{
			return in_array($key, $other_key) ? $ret_if_true : $ret_if_false;
		} else if ((is_object($other_key) && in_array('ArrayAccess', class_implements($other_key))))
		{
			foreach($other_key as $obj)
			{
				if ($obj->$value_var == $key)
				{
					return $ret_if_true;
				}
			}

			// we didn't find it, return.
			return $ret_if_false;

		} else {
			return ($key == $other_key) ? $ret_if_true : $ret_if_false;
		}
	}

	/**
	 * Uniformly format some filesizes
	 * @param   $number float   the filesizes to format
	 * @return		  string  the formatted filesizes
	 */
	public static function file_size($size) { 
		$places = Settings::get('DecimalPlaces');
		$dp = Settings::get('DecimalPoint');
		$ts = Settings::get('ThousandsSeparator');
		return number_format((double) round($size/1024), 0, $dp, $ts).MSG_KB;
	}

	/**
	 * Convert priorities from nubmers to messages.
	 * @param   $priority   string   the priority to format
	 * @return			  string  the priority as a string
	 */
	public static function convert_priority($priority)
	{
		switch ($priority) 
		{
			case "0": return MSG_PRIORITY_LOW;	break;
			case "1": return MSG_PRIORITY_NORMAL; break;
			case "2": return MSG_PRIORITY_HIGH;   break;
			default:  return MSG_PRIORITY_NORMAL;
		}
	}	
}
