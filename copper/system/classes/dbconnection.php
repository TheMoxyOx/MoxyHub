<?php
// $Id$ 
class DBConnection
{
    // This is the MYSQL DB Connection object.
    // move the system over to ADODB at a later date?

    var $Engine;

    // public variables
    var $ID;
    var $LastErrorMessage = '';
    var $LastErrorNumber  = 0;
    var $Persistent       = 0;
    var $FatalDBErrors    = 0;

    // private variables
    var $_Server;
    var $_Username;
    var $_Password;
    var $_Database;
    var $_State = 0;

    /**
     * @return DBConnection
     * @param server = null unknown
     * @param username = null unknown
     * @param password = null unknown
     * @param database = null unknown
     * @param persistent = null unknown
     * @desc Enter description here...
     */
    function DBConnection($server = null, $username = null, $password = null, $database = null, $persistent = 0)
    {
        //$this->Engine = DB_DRIVER;
        $this->Persistent = $persistent;
        if (($server != null) && ($username != null) && ($database != null))
        {
            $this->Open($server, $username, $password, $database);
        }
    }

    /**
     * @return unknown
     * @param server unknown
     * @param username unknown
     * @param password unknown
     * @param database unknown
     * @desc Enter description here...
     */
    function Open($server, $username, $password, $database)
    {
        $this->_Server   = $server;
        $this->_Username = $username;
        $this->_Password = $password;
        $this->_Database = $database;

        if ($this->Persistent == 1)
        {
            // open a persistent connection
            $this->ID = @mysql_pconnect($this->_Server, $this->_Username, $this->_Password);
        }
        else
        {
            // open a normal connection
            $this->ID = @mysql_connect($this->_Server, $this->_Username, $this->_Password);
        }

        if ($this->ID)
        {
            if (@mysql_select_db($this->_Database))
            {
                //Strict mode bypass
                @mysql_query("set sql_mode = ''");
                $this->_State = 1;
                return true;
            }
        }

        # Nodens
        define('CU_ERR_DB_1000',        'There was a fatal error connecting to the database.<br/>Error code: %s<br/>Error message: %s');
        exit(sprintf(CU_ERR_DB_1000, mysql_errno(), mysql_error()));
    }

    /**
     * @return bool
     * @desc Enter description here...
     */
    function Close()
    {
        if ($this->_State)
        {
            @mysql_close($this->ID);
            $this->_State = 0;
            return true;
        }
        return false;
    }

    function State()
    {
        return $this->_State;
    }

    /**
     * @return unknown
     * @param SQL unknown
     * @desc Enter description here...
     */
    function Execute($SQL)
    {
        $this->QueryPrepare($SQL);
        if (!$this->_State)
        {
            $this->throwDBError($SQL);
        }
        else
        {
            if (!@mysql_query($SQL, $this->ID))
            {
                $this->throwDBError($SQL);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @return unknown
     * @param SQL unknown
     * @desc Enter description here...
     */
    function Exists($SQL)
    {
        //$SQL = str_replace("tbl", DB_PREFIX.'tbl', $SQL);
        //$SQL = str_replace("sys", DB_PREFIX.'sys', $SQL);

        if ($this->_State)
        {
            $result = $this->QuerySingle($SQL);
            if (is_array($result))
            {
                unset($result);
                return true;
            }
        }

        return false;
    }

    /**
     * @return unknown
     * @param strSQL unknown
     * @param associative = null unknown
     * @desc Enter description here...
     */
    function ExecuteScalar($strSQL)
    {
        $this->QueryPrepare($strSQL);

        if ($this->_State)
        {
            if ($query = @mysql_query($strSQL, $this->ID))
            {
                $result = mysql_fetch_row($query);
                return $result[0];
            }
            else
            {
                $this->throwDBError($strSQL);
            }
        }
        return false;
    }

    function NumRows($recordSet)

    {
        if ($this->_State) {
            $result = mysql_num_rows($recordSet);
            return $result;
        }
    }
    /**
     * @return unknown
     * @param strSQL unknown
     * @param associative = null unknown
     * @desc Enter description here...
     */
    function QuerySingle($strSQL, $associative = 1)
    {
        $this->QueryPrepare($strSQL);

        if ($this->_State)
        {
            $query = @mysql_query($strSQL, $this->ID);
            if (!$query)
            {
                $this->throwDBError($strSQL);
                return false;
            }
            else
            {
                switch ($associative) {
                    case 0: return @mysql_fetch_row($query);break;
                    case 1: return @mysql_fetch_array($query);break;
                    case 2: return @mysql_fetch_assoc($query);break;
                }
            }

        }
        return false;
    }

    /**
     * @return unknown
     * @param strSQL unknown
     * @param associative = null unknown
     * @desc Enter description here...
     */
    function Query($strSQL, $associative = 1)
    {
        $this->QueryPrepare($strSQL);

        if ($this->_State)
        {
            $query = @mysql_query($strSQL, $this->ID);
            if (!$query)
            {
                $this->throwDBError($strSQL);
                return false;
            }
            else
            {
                $rows = @mysql_num_rows($query);
                $result = array();
                if ($rows > 0)
                {
                    for($x = 0; $x < $rows; $x++)
                    {
                        switch ($associative)
                        {   
                            case 0: $result[$x] = @mysql_fetch_row($query); break;
                            case 1: $result[$x] = @mysql_fetch_array($query); break;
                            case 2: $result[$x] = @mysql_fetch_assoc($query); break;
                        }
                    }
                    mysql_free_result($query);
                } else {
                    return array();
                }
                return $result;
            }
        }
        return false;
    }

    /**
     * @return unknown
     * @param string unknown
     * @desc Enter description here...
     */
    function Prepare($string)
    {
        if (!get_magic_quotes_gpc())
        {
            $string = addslashes($string);
        }
        return trim($string);
    }

    /**
     * @return unknown
     * @param string unknown
     * @desc Enter description here...
     */
    function QueryPrepare(&$string)
    {
/*
        $string = str_replace("tblTasks", DB_PREFIX.'tblTasks', $string);
        $string = str_replace(" tbl", ' '.DB_PREFIX.'tbl', $string);
        $string = str_replace(",tbl", ','.DB_PREFIX.'tbl', $string);
        $string = str_replace(" sys", ' '.DB_PREFIX.'sys', $string);
        $string = str_replace(",sys", ','.DB_PREFIX.'sys', $string);
*/
    }

    /**
     * show database errors and halt
     */
    function throwDBError($SQL)
    {
        $this->LastErrorMessage = mysql_error($this->ID);
        $this->LastErrorNumber  = mysql_errno($this->ID);
        if ($this->FatalDBErrors == 0) 
            return;

        printf("<h2>Internal Error</h2>\n");
        printf("<b>Error %s occurred updating the Copper database:</b><br /><br />\n", $this->LastErrorNumber);
        print(nl2br(htmlspecialchars($this->LastErrorMessage))."<br /><br />\n");
        printf("<pre>%s</pre>\n", $SQL);
        exit();
    }
}
 
