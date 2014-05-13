<?php
/**
 * day class
 * $Id $
 */

class Day extends Item
{
	protected $tableName = 'tblDay';
	
	protected $default_fields = array(
		'ID',
		'Epoch',
		'Day',
		'Month',
		'Year',
		'Weekday',
	);

	protected $default_data = array(
	);

	// ie yyyy-mm-dd.
	public static function create_from_iso8601($date)
	{
		list($y, $m, $d) = explode('-', $date);
		return new Day(array('Year' => $y, 'Month' => $m, 'Day' => $d));
	}
	
}

