<?php
/**
 * Base Database items, for multiple of the same time.
 * $Id$
 */

abstract class Items extends ArrayObject
{
	// some defaults, coz we're nice.
	private $items	= null; 

	// be sure to set the following when extending
	// private $className = null;
	// private $tableName = null;

	const WHERE_AND = 1;
	const WHERE_OR	= 2;

	public function __construct($params = array())
	{
		$db_params = array();

		// let's do any where conditions.
		if ( isset( $params['sql_where'] ) )
		{
			$where = $params['sql_where'];
			// see if we passed in any values too.
			if ( isset( $params['sql_where_values'] ) ) 
			{
				if ( is_array( $params['sql_where_values'] ) )
				{
					$db_params = $db_params + $params['sql_where_values'];
				} else {
					$db_params[] = $params['sql_where_values'];
				}
			}

		} else if ( isset( $params['where'] ) && is_array( $params['where'] ) && count( $params['where'] ) > 0 ) 
		{
			$strArray = array_map( create_function( '$a', 'return $a . " = ?"; '), array_keys( $params['where'] ) );

			// default to AND
			if ( isset( $params['where_logic'] ) && $params['where_logic'] == self::WHERE_OR )
			{
				$glue = ' OR ';
			} else {
				$glue = ' AND ';
			}
			
			$where = " WHERE " . implode($glue, $strArray);
			// add to the params to be passed in the query.
			$db_params = $db_params + array_values( $params['where'] );
		} else 
		{
			// default is empty.
			$where = '';
		}
		
		// now we do groupby
		if ( isset( $params['sql_groupby'] ) )
		{
			$groupby = $params['sql_groupby'];

			if ( is_array( $params['sql_groupby_values'] ) )
			{
				$db_params = $db_params + $params['sql_groupby_values'];
			} else {
				$db_params[] = $params['sql_groupby_values'];
			}

		} else if ( isset( $params['groupby'] ) )
		{
			$groupby = ' GROUP BY ';
			if (is_array($params['groupby']))
			{
				$groupby .= implode( ' ', $params['groupby'] );
			} else {
				// assume a straight field.
				$groupby .= $params['groupby'];
			}
		} else {
			$groupby = '';
		}
		
		// now we do orderbys
		if ( isset( $params['sql_orderby'] ) )
		{
			$orderby = $params['sql_orderby'];

			if ( is_array( $params['sql_orderby_values'] ) )
			{
				$db_params = $db_params + $params['sql_orderby_values'];
			} else {
				$db_params[] = $params['sql_orderby_values'];
			}

		} else if ( isset( $params['orderby'] ) )
		{
			$orderby = ' ORDER BY ';
			if (is_array($params['orderby']))
			{
				$orderby .= implode( ', ', $params['orderby'] );
			} else {
				// assume a straight field.
				$orderby .= $params['orderby'];
			}
		} else {
			$orderby = '';
		}
		
		// you have to know your limits
		if ( isset( $params['sql_limit'] ) )
		{
			$limit = $params['sql_limit'];
			
			if ( is_array( $params['sql_limit_values'] ) )
			{
				$db_params = $db_params + $params['sql_limit_values'];
			} else {
				$db_params[] = $params['sql_limit_values'];
			}
			
		} else if ( isset( $params['limit'] ) )
		{
			$limit = ' LIMIT ';
			if ( is_array( $params['limit'] ) )
			{
				if ( ( count( $params['limit'] ) == 1) && is_numeric( $params['limit'][0] ) ) {
					$limit .= $params['limit'][0];
				} else if ( ( count( $params['limit'] ) == 2) && is_numeric( $params['limit'][0] ) && is_numeric( $params['limit'][1] ) ) {
					$limit .= implode( ',', $params['limit'] );
				} else {
					// okay not sure what's going on here, but it sure seems wacky. drop the limit bit.
					$limit = '';
				}

			} else {
				// assume a straight number submission.
				$limit .= $params['limit'];
			}
		} else {
			$limit = '';
		}
		
		// finally, allow a completely custom sql, but still allow orderby's and limits.
		if ( isset( $params['sql'] ) ) 
		{
			$sql = $params['sql'] . implode(' ', array($orderby, $limit));
			// iff we have custom sql, we might have custom data.
			if ( isset( $params['db_params'] ) )
			{
				$db_params = $params['db_params'];
			}
		} else {
			// build up the query from the above parts.
			$sql = "SELECT * FROM " . implode(' ', array($this->tableName, $where, $groupby, $orderby, $limit));
		}

		$pdo_s = DB::q($sql, $db_params);

		if ( Debug::chk(DEBUG_SQL_ALL) )
		{
			Debug::debug_sql($pdo_s, $sql, $db_params);
		}

		if ($pdo_s === FALSE)
		{
			// uh oh. something was wrong with the query. set the array to an empty array.
			parent::__construct(array());

			if ( Debug::chk(DEBUG_SQL_ERROR) )
			{
				Debug::debug_sql($pdo_s, $sql, $db_params);
			}

		} else {
			// pass in null, as this stops the item base class from getting the item again
			$pdo_s->setFetchMode(PDO::FETCH_CLASS, $this->className, array(null));
		
			$items = $pdo_s->fetchAll();

			// as pdo calls the magic setter, we update so that the class is internally consistent.
			// see the item class for more details
			foreach($items as $item)
			{
				$item->finish_pdo_fetch();
			}

			parent::__construct($items);

		}
	}

	public function __get($var)
	{
		// if we only have 1 item, then emulate the functionality of a single item.
		if (count($this) == 1)
		{
			return $this[0]->$var;
		}
	}
}

