<?php
// $Id$
class DBPagedRecordset extends DBRecordset
{
    var $TotalRecords = 0;
    var $RecordLimit;
    var $RecordStart;

    /**
     * @return unknown
     * @param strSQL unknown
     * @param $DB unknown
     * @param limit = null unknown
     * @param offset = null unknown
     * @desc Enter description here...
     */
    function Open($strSQL, &$DB, $limit = 15, $offset = 0)
    {
        $this->QueryPrepare($strSQL);
    
        $return = false;
        $this->RecordLimit = $limit;
        $this->RecordStart = $offset;
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
                $this->TotalRecords = $this->_Rows;
                // we have our result. now we're going to compare
                if ($this->_Rows > 0)
                {
                    // we have records.
                    // first check that the start offset, is not greater or = _Rows
                    // ok - set x to Start, Inc till <= Offset = false
                    if ($this->RecordStart < $this->_Rows)
                    {
                        mysql_data_seek($query, $this->RecordStart);
                        for ($x = 0; $x < $this->RecordLimit; $x++)
                        {
                               if ($this->_Result[$x] = mysql_fetch_array($query))
                               {
                                   $this->_Rows = $x + 1;
                            }
                        }
                        $this->_State = 1;
                        $return = true;
                    }
                }
                mysql_free_result($query);
            }
        }
        return $return;
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
 
