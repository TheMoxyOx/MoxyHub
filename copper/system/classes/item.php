<?php
/**
 * Base Database item
 * $Id$
 */

abstract class Item
{
	// some defaults, coz we're nice.
	private $idColumn 	= 'ID';
	private $dbData			= array();
	private $newData		= array();
	public	$exists			= FALSE;

	public function __construct($id = null)
	{
		if ($id == null) {
			return;
		}

		if ( is_numeric($id) )
		{
			// where we ust got a numeric id.
			$q = "SELECT * FROM " . $this->tableName . " WHERE " . $this->idColumn . " = ?";
			$params = array($id);
			
		} else if ( is_array($id) && ( count($id) > 0 ) ) {
			// this is for when we have non-numeric unique indexes.
			$q = "SELECT * FROM " . $this->tableName . " WHERE ";

			$crits = array();
			$data = array();
			foreach($id as $key => $val) {
				$crits[].= $key . " = ? ";
			}
			
			$q .= implode(' AND ', $crits) . ' LIMIT 1';
			$params = array_values($id);
		} else {
			return;
		}
		
		$pdo_s = DB::q($q, $params);
				
		if (($pdo_s === FALSE) || ($pdo_s->rowCount() != 1))
		{
			// hrmm.. we didn't get no data. oh well, at least store the requested data in the newData array.
			if ( is_numeric($id) )
			{
				// where we ust got a numeric id.
				$this->{$this->$idColumn} = $id;

			} else if ( is_array($id) && ( count($id) > 0 ) ) {
				foreach($id as $key => $val) {
					$this->$key = $val;
				}
			}

			return;
		} else {
			$data = $pdo_s->fetch(PDO::FETCH_ASSOC);

			if ($data !== FALSE)
			{
				$this->dbData = $data;
				// set newdata as well. we need to do this so that we can compare if we are trying to _remove_ data, ie like settin ga field to null
				$this->newData = $data;
				$this->exists = TRUE;
			}
		}
	}

	public function __get($field)
	{
		if (is_array($this->newData) && isset($this->newData[$field]))
		{
			return $this->newData[$field];
		} else if (is_array($this->newData) && ($this->newData[$field] === null) && ($this->dbData[$field] !== null)) 
		{
			// this case is funny. it's when we've cleared the data in newData, but dbData used to have something!. i.e., we are 
			// trying to clear data in the db. so this should return null, the new 'value'
			// note that we _actually_ deal with it in check_set_null below.
			return null;
		} else if (is_array($this->dbData) && isset($this->dbData[$field])) 
		{
			return $this->dbData[$field];
		} else if (is_array($this->default_data) && isset($this->default_data[$field])) 
		{
			return$this->default_data[$field];
		} else {
			return null;
		}
	}
	
	public function __set($field, $data)
	{
		/* hokay so in copper, becuase it's retarded, straight sprintf string replacement queries _require_ magic quotes still for their *ahem* "security".
		** But magic quotes are retarded. So _we're_ going to strip them internally, because only at _this_ point to we know they aren't going to be
		** needed. 
		** so -amazingly- retarded.
		*/
		$this->newData[$field] = Request::clean_magic_quotes($data);
	}

	private function check_set_null($field)
	{
		return (is_array($this->newData) && ($this->newData[$field] === null) && ($this->dbData[$field] !== null));
	}

	/** 
	 * Commit new data to the db. Also update the class variables if successful.
	 */
	public function commit()
	{
		$q_type = ($this->exists) ? 'UPDATE' : 'INSERT';
		$q = $q_type . ' ' . $this->tableName . " SET ";

		// there's an assumption in here that passing sql an ID = null will trigger auto-increment. this is true in mysql, don't know about other dbs
		$vals = array();
		$bits = array();
		foreach($this->get_default_fields() as $f)
		{
			if ($this->$f !== null)
			{
				$bits[] = $f . ' = ? ';
				$data[] = $this->$f;
			} else if ($this->check_set_null($f)) {
				$bits[] = $f . ' = null ';
			}
		}
		
		$q .= implode(', ', $bits);

		if ($this->exists) {
			// specify which one to update.
			$q .= " WHERE " . $this->idColumn . " = ? ";
			$data[] = $this->ID;
		}

		$success = DB::q($q, $data);

		if ($success !== FALSE)
		{
			$this->exists = TRUE;

			if ($q_type == 'INSERT')
			{
				// if we inserted, the database may have supplied data (like, but not limited to, 'id'). 
				// therefore we need to fetch again from the database.
				$id = DB::last_insert_id();
				self::__construct($id);
			} else {
				// if it's an update, we can just merge.
				$this->dbData = array_merge($this->dbData, $this->newData);
			}
			
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	public function delete() 
	{
		if ($this->is_virtual === TRUE)
		{
			Debug::d(DEBUG_BASIC_ERRORS, "Shouldn't be calling Item::delete() on a virtual object.");
			return TRUE;
		}

		if ($this->exists)
		{
			$q = "DELETE FROM " . $this->tableName . " WHERE " . $this->idColumn . " = ?";
			$data = array($this->{$this->idColumn});
			$pdo_s = DB::q($q, $data);
			
			if ( Debug::chk(DEBUG_SQL_ALL) )
			{
				Debug::debug_sql($pdo_s, $q, $data);
			}

			if ($pdo_s === FALSE)
			{
				$retval = FALSE;
			} else {
				$retval = TRUE;
				$pdo_s->closeCursor();
			}
			
		} else {
			$retval = FALSE;
		}
		
		return $retval;
	}

	/** 
	 * Internal use only. Moves data form newData to dbData, for use in the items multiclass.
	 * This is used because it is actually db data, but the magic setter that is called by pdo puts the data in newData.
	 */
	public function finish_pdo_fetch()
	{
		$this->dbData = $this->newData;
		$this->exists = TRUE;
	}

	public function get_default_fields()
	{
		return is_array($this->defaultFields) ? $this->defaultFields : array();
	}
	
	public function update_from_postdata($data_map)
	{
		// this is as good a place as any to strip of slashes. Stupid copper.
		// once we get rid of magic quotes, we can get rid of strip slashes.
		foreach($data_map as $key => $post_key)
		{
			$this->__set($key, stripslashes(Request::post($post_key)));
		}
	}
	
	public function dump_newdata()
	{
		var_dump($this->newData);
	}

}

