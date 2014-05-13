<?php
// $Id$
class DBDelete
{
    var $Message = '';
    var $ActuallyDelete = 0;
    var $ID;
    var $Module;
    var $DB;

    /**
     * @return DBDelete
     * @param $DB unknown
     * @param module unknown
     * @param id unknown
     * @desc Enter description here...
     */
    function &DBDelete(&$DB, $start_table, $record_id)
    {
        $this->DB =& $DB;
        $this->Module = $module;
        $this->ID = $id;
        echo 'DBDelete Start<br />';

        echo 'DBDelete Finish<br />';
        echo is_object($this).'<br />';
    }

    function getMessage()
    {
        return $this->Message;
    }

    /**
     * @return void
     * @desc Enter description here...
     */
    function Execute()
    {
        $projects = $this->DB->Query(DBDeleteSQL::ListProjectsForClient($id), 0);
        if (is_array($projects))
        {
            // we have projects
            $message = 'there are ' . count($projects) . ' projects<br />';
            $projects_list = join(',', $projects[0]);
            $message .= 'ProjectID List: ' . $projects_list . '<br />';

            // do files list
            $files = $this->DB->Query("SELECT file_id, file_project, file_real_filename FROM tblFiles WHERE file_project IN ($projects_list) ORDER BY file_id ASC", 0);
            if (is_array($files))
            {
                $message .= 'there are ' . count($files) . ' files for the projects<br />';
                $files_list = array();
                for ($i = 0, $c = count($files); $i < $c; $i++)
                {
                    $files_list[] .= SYS_FILEPATH . $files[$i][1] .'/'. $files[$i][2];
                }
                $message .= 'FileID List: ' . count($files_list) . '<br />';
                for ($i = 0; $i < count($files_list); $i++)
                {
                    $message .= $files_list[$i];
                }
            }

            // do tasks list
        }
        else
        {
            if ($this->ActuallyDelete)
            {
                $this->DB->Execute(DBDeleteSQL::DeleteClient($id));
            }
        }
    }
}

class DBDeleteSQL
{
    /**
     * @return unknown
     * @param id unknown
     * @desc Enter description here...
     */
    function DeleteClient($id)
    {
        switch (DB_DRIVER)
        {
            case 'mysql' : return sprintf('DELETE FROM tblClients WHERE id = \'%s\'', $id);
        }
    }

    /**
     * @return unknown
     * @param id unknown
     * @desc Enter description here...
     */
    function DeleteProject($id)
    {
        switch (DB_DRIVER)
        {
            case 'mysql' : return sprintf('DELETE FROM tblProjects WHERE id = \'%s\'', $id);
        }
    }

    /**
     * @return unknown
     * @param id unknown
     * @desc Enter description here...
     */
    function ListProjectsForClient($id)
    {
        switch (DB_DRIVER)
        {
            case 'mysql' : return sprintf('SELECT project_id FROM tblProjects WHERE project_client = \'%s\' ORDER BY project_id ASC', $id);
        }
    }
}

function clr_dir($dir) {
    if(@ ! $opendir = opendir($dir)) {
        return false;
    }
    while(false !== ($readdir = readdir($opendir))) {
        if($readdir !== '..' && $readdir !== '.') {
            $readdir = trim($readdir);
            if(is_file($dir.'/'.$readdir)) {
                if(@ ! unlink($dir.'/'.$readdir)) {
                    return false;
                }
            } elseif(is_dir($dir.'/'.$readdir)) {
               // Calls itself to clear subdirectories
               if(! clr_dir($dir.'/'.$readdir)) {
                   return false;
                }
            }
        }
    }
    closedir($opendir);
    if(@ ! rmdir($dir)) {
        return false;
    }
    return true;
}

function delete_all_from_dir($Dir){
       // delete everything in the directory
    if ($handle = @opendir($Dir)) 
    {
        while (($file = readdir($handle)) !== false) 
        {
            if ($file == "." || $file == "..") 
            {
                continue;
            }
            if (is_dir($Dir.$file))
            {
                // call self for this directory
                delete_all_from_dir($Dir.$file.'/');
                chmod($Dir.$file, 0777);
                rmdir($Dir.$file); //remove this directory
            }
            else
            {
                chmod($Dir.$file,0777);
                unlink($Dir.$file); // remove this file
            }
        }
    }
    @closedir($handle);
}

 
