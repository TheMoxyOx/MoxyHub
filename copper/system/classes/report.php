<?php
/**
 * Base Report class
 * $Id$
 */

// work report has been migrationed, project report, not yet.
abstract class Report extends Item
{
	protected $default_data = array(
	);
	
	private static function date_format($format_number) {
		$date_format = array('1' => 'Y-m-d', '2' => 'Y-d-m', '3' => 'd-m-Y', '4' => 'm-d-Y');
		return $date_format[$format_number];
	}
	
	public $period_options = array(
		'today' => MSG_TODAY,
		'yesterday' => MSG_YESTERDAY,
		
		'thisweek' => MSG_THIS_WEEK,
		'lastweek' => MSG_LAST_WEEK,
		
		'thismonth' => MSG_THIS_MONTH,
		'lastmonth' => MSG_LAST_MONTH,
		'nextmonth' => MSG_NEXT_MONTH,
		
		'thisyear' => MSG_THIS_YEAR,
		'lastyear' => MSG_LAST_YEAR,
	);

	abstract function run();
	// Exports the data in CSV format.
	abstract function exportCSV($myob = FALSE);

	// Get the start date, taking into account the period var. if period is set, then we use that.
	// allow caller to specify a period, so we can do this all in one place.
	// otherwise, use the stored date. 
	// if neither, return null.
	public function getStartDate($period = null, $format_option = null)
	{
		if ($period == null)
		{
			$period = $this->Period;
		}
		
		if ( $period ) 
		{
			$dow = ( date( 'w' ) == 0 ) ? 7 : date( 'w' );  // Emulate date( 'N' ) for PHP versions < 5.1.0

			if ($format_option == null)
			{
				$format_type = Settings::get('DateFormat');
			}
			else {
				$format_type = $format_option;
			}
			$format = self::date_format($format_type);
	
			switch ( $period ) 
			{
				case 'today': 
					return date($format);
				case 'yesterday': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) );
				case 'thisweek': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 1, date( 'Y' ) ) );
				case 'lastweek': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow - 6, date( 'Y' ) ) );
				case 'thismonth':
					return date(str_replace('d', '01', $format));
				case 'lastmonth': 
					return date(str_replace('d', '01', $format), mktime( 0, 0, 0, date( 'm' ) - 1, 1, date( 'Y' ) ) );
				case 'nextmonth': 
					return date(str_replace('d', '01', $format), mktime( 0, 0, 0, date( 'm' ) + 1, 1, date( 'Y' ) ) );
				case 'thisyear': 
					return date(str_replace('m', '01', str_replace('d', '01', $format)));
				case 'lastyear': 
					return date(str_replace('m', '01', str_replace('d', '01', $format)), mktime( 0, 0, 0, 1, 1, date( 'Y' ) - 1 ) );
				default: 
					return date($format, mktime( 0, 0, 0, date( 'm' ) - 1, date( 'd' ), date( 'Y' ) ) );
			}
		} else if ($this->StartDate)
		{
			return $this->StartDate;
		} else {
			return null;
		}
	}
	
	public function getEndDate($period = null, $format_option = null)
	{
		if ($period == null)
		{
			$period = $this->Period;
		}
		
		if ($format_option == null)
		{
			$format_type = Settings::get('DateFormat');
		}
		else {
			$format_type = $format_option;
		}
		$format = self::date_format($format_type);
		
		
		if ( $period )
		{
			$dow = ( date( 'w' ) == 0 ) ? 7 : date( 'w' );  // Emulate date( 'N' ) for PHP versions < 5.1.0
			switch ( $period ) 
			{
				case 'today': 
					return date($format);
				case 'yesterday': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) );
				case 'thisweek': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 7, date( 'Y' ) ) );
				case 'lastweek': 
					return date($format, mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow, date( 'Y' ) ) );
				case 'thismonth': 
					return date(str_replace('d', 't', $format));
				case 'lastmonth': 
					return date(str_replace('d', 't', $format), mktime( 0, 0, 0, date( 'm' ) - 1, 1, date( 'Y' ) ) );
				case 'nextmonth': 
					return date(str_replace('d', 't', $format), mktime( 0, 0, 0, date( 'm' ) + 1, 1, date( 'Y' ) ) );
				case 'thisyear': 
					return date(str_replace('m', '12', str_replace('d', '31', $format)));
				case 'lastyear': 
					return date(str_replace('m', '12', str_replace('d', '31', $format)), mktime( 0, 0, 0, 1, 1, date ('Y' ) - 1 ) );
				default: 
					return date($format);
			}
		} else if ($this->EndDate)
		{
			return $this->EndDate;
		} else {
			return null;
		}
	}
}
 
