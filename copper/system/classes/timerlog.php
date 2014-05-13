<?php
/**
 * Activity Log class
 * $Id $
 */

class TimerLog extends Item
{
	protected $tableName = 'tblTimerLog';
	
	protected $defaultFields = array(
		'ID',
		'Updated',
		'UserID',
		'TaskID',
		'Elapsed',
		'Paused',
	);

	protected $default_data = array(
	);

	public function __get($var)
	{
		if ($var == 'ProjectID')
		{
			$this->task->ProjectID;
		} else if ($var == 'task')
		{
			return new Task($this->TaskID);
		} else if ($var == 'hours')
		{
			$parts = explode(':', $this->Elapsed);
			return (int) $parts[0];
		} else if ($var == 'minutes')
		{
			$parts = explode(':', $this->Elapsed);
			return (int) $parts[1];
		} else if ($var == 'seconds')
		{
			$parts = explode(':', $this->Elapsed);
			return (int) $parts[2];
		} else if ($var == 'elapsed_time')
		{
			$time = 0;
			$time = ($this->hours * 60 * 60) + ($this->minutes * 60) + $this->seconds;
			if ($this->Paused == 1)
			{
				return $time;
			} else
			{
				return $time + time() - strtotime($this->Updated);
			}
		} else if ($var == 'elapsed_hours')
		{
			return intval($this->elapsed_time / 60 / 60);
		} else if ($var == 'elapsed_minutes')
		{
			return intval(($this->elapsed_time / 60) % 60);
		} else if ($var == 'elapsed_seconds')
		{
			return intval($this->elapsed_time % 60);
		} else if ($var == 'elapsed_format')
		{
			return str_pad($this->elapsed_hours, 2, "0", STR_PAD_LEFT) . ':' . str_pad($this->elapsed_minutes, 2, "0", STR_PAD_LEFT) . ':' . str_pad($this->elapsed_seconds, 2, "0", STR_PAD_LEFT);
		} else
		{
			return parent::__get($var);
		}
		
	}
}
