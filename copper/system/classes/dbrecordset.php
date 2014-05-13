<?php
// $Id$
class DBRecordset
{
    // private variables
    var $_State     = 0;
    var $_Record     = 0;
    var $_Rows         = 0;
    var $_Result     = null;

    /**
     * @return unknown
     * @param strSQL unknown
     * @param $DB unknown
     * @desc Enter description here...
     */
    function Open($strSQL, &$DB)
    {
        $this->QueryPrepare($strSQL);
    
        $return = false;
        if ($DB->State())
        {
            $query = mysql_query($strSQL, $DB->ID);
            if (!$query)
            {
                $DB->LastErrorMessage = mysql_error($DB->ID);
                $DB->LastErrorNumber = mysql_errno($DB->ID);
            }
            else
            {
                $this->_Rows = mysql_num_rows($query);
                if ($this->_Rows > 0)
                {
                    for($x = 0; $x < $this->_Rows; $x++)
                    {
                        $this->_Result[$x] = mysql_fetch_array($query);
                    }
                    $this->_State = 1;
                    $return = true;
                }
                mysql_free_result($query);
            }
        }
        return $return;
    }


    /**
     * @return void
     * @desc Enter description here...
     */
    function Close()
    {
        unset($this->_Result);
        $this->_State = 0;
        $this->_Record = 0;
        $this->_Rows = 0;
        $this->_Result = null;
        return;
    }

    /**
     * @return unknown
     * @desc Enter description here...
     */
    function State()
    {
        return $this->_State;
    }

    /**
     * @return unknown
     * @param field unknown
     * @desc Enter description here...
     */
    function Field($field)
    {
        if (isset($this->_Result[$this->_Record][$field]))
        {
            return $this->_Result[$this->_Record][$field];
        }
        //return "ERROR: Field Does Not Exist";
        return '';
    }

    /**
     * @return unknown
     * @desc Enter description here...
     */
    function EOF()
    {
        if ( ($this->_State == 1) && ($this->_Record < $this->_Rows) )
        {
               return false;
        }
        return true;
    }

    /**
     * @return unknown
     * @desc Enter description here...
     */
    function Count()
    {
        return $this->_Rows;
    }

    /**
     * @return void
     * @desc Enter description here...
     */
    function MoveNext()
    {
        $this->_Record += 1;
        //return true;
    }

    /**
     * @return void
     * @desc Enter description here...
     */
    function MovePrev()
    {
        $this->_Record -= 1;
        //return true;
    }

    /**
     * @return void
     * @desc Enter description here...
     */
    function MoveFirst()
    {
        $this->_Record = 0;
        //return true;
    }

    /**
     * @return void
     * @desc Enter description here...
     */
    function MoveLast()
    {
        $this->_Record = $this->_Rows - 1;
        //return true;
    }

    /**
     * @return unknown
     * @param string unknown
     * @desc Enter description here...
     */
    function QueryPrepare(&$string)
    {
        $string = str_replace(" tbl", ' '.DB_PREFIX.'tbl', $string);
        $string = str_replace(",tbl", ','.DB_PREFIX.'tbl', $string);
        $string = str_replace(" sys", ' '.DB_PREFIX.'sys', $string);
        $string = str_replace(",sys", ','.DB_PREFIX.'sys', $string);
    }

}
 
