<?php
// $Id$
class User
{
    var $ID            = 0;
    var $Authorised    = 0;
    var $Username      = '';
    var $Firstname     = '';
    var $Lastname      = '';
    var $Fullname      = '';
    var $PasswordHash  = '';
    var $Email         = '';
    var $Phone         = array('Phone1' => '', 'Phone2' => '', 'Phone3' => '');
    var $Module        = NULL;
    var $AssignedTasks = NULL;
    var $MenuList      = array();
    var $ModuleList    = array();
    var $EmailNotify   = 1;
    var $CostRate      = NULL;
    var $ChargeRate    = NULL;
    var $IsAdmin       = FALSE;
    var $ModulePermissions  = NULL;
    var $ClientPermissions  = NULL;
    var $ProjectPermissions = NULL;

    function Initialise($user = 0, &$DB)
    {
        if ($user != 0)
        {
            $this->ModulePermissions = NULL;
            $this->ClientPermissions = NULL;
            $this->ProjectPermissions = NULL;
            $this->AssignedTasks = NULL;
            $this->ID = $user;
            $this->Authorised = 1;

            // Get default cost/charge rate.
            $defaultRate = $DB->ExecuteScalar(sprintf(CU_SQL_GET_DEFAULT_RATE));

            // get user details
            $SQL = sprintf(CU_SQL_USER_DETAILS, $this->ID);
            $result = $DB->QuerySingle($SQL);
            if (is_array($result))
            {
                $this->Username        = $result['Username'];
                $this->Firstname       = $result['FirstName'];
                $this->Lastname        = $result['LastName'];
                $this->Fullname        = $result['FirstName'].' '.$result['LastName'];
                $this->PasswordHash    = $result['Password'];
                $this->Email           = $result['EmailAddress'];
                $this->Phone['Phone1'] = $result['Phone1'];
                $this->Phone['Phone2'] = $result['Phone2'];
                $this->Phone['Phone3'] = $result['Phone3'];
                $this->Module          = $result['Module'];
                $this->EmailNotify     = $result['EmailNotify'];
                $this->CostRate        = ($result['CostRate'] > 0.00) ? $result['CostRate'] : $defaultRate;
                $this->ChargeRate      = ($result['ChargeRate'] > 0.00) ? $result['ChargeRate'] : $defaultRate;
            }

            //MODULES
            //Create array of module permissions
            if (0 == PERM_ORDER) 
            {
                $sql_first = sprintf(CU_SQL_USER_MODULE_PERMISSIONS, $this->ID);
                $sql_override = sprintf(CU_SQL_GROUP_MODULE_PERMISSIONS, $this->ID);
            }
            else 
            {
                $sql_first = sprintf(CU_SQL_GROUP_MODULE_PERMISSIONS, $this->ID);
                $sql_override = sprintf(CU_SQL_USER_MODULE_PERMISSIONS, $this->ID);
            }
            
            $result_first = $DB->Query($sql_first);
            $result_override = $DB->Query($sql_override);
            $this->ModulePermissions = $this->CreateArray($result_first, $result_override);
            
            //CLIENTS
            //Create array of client perms
            if (0 == PERM_ORDER) 
            {
                $sql_first      = sprintf(CU_SQL_USER_OBJECT_PERMISSIONS, $this->ID, 'clients');
                $sql_override   = sprintf(CU_SQL_GROUP_OBJECT_PERMISSIONS, $this->ID, 'clients');
            }
            else {
                $sql_first      = sprintf(CU_SQL_GROUP_OBJECT_PERMISSIONS, $this->ID, 'clients');
                $sql_override   = sprintf(CU_SQL_USER_OBJECT_PERMISSIONS, $this->ID, 'clients');
            }

            $result_first = $DB->Query($sql_first);
            $result_override = $DB->Query($sql_override);
            $this->ClientPermissions = $this->CreateObjectArray($result_first, $result_override);

            //PROJECTS
            //Create array of project perms
            if (0 == PERM_ORDER) {
                $sql_first      = sprintf(CU_SQL_USER_OBJECT_PERMISSIONS, $this->ID, 'projects');
                $sql_override   = sprintf(CU_SQL_GROUP_OBJECT_PERMISSIONS, $this->ID, 'projects');
            }
            else {
                $sql_first      = sprintf(CU_SQL_GROUP_OBJECT_PERMISSIONS, $this->ID, 'projects');
                $sql_override   = sprintf(CU_SQL_USER_OBJECT_PERMISSIONS, $this->ID, 'projects');
            }

            $result_first = $DB->Query($sql_first);

            $result_override = $DB->Query($sql_override);
            $this->ProjectPermissions = $this->CreateObjectArray($result_first, $result_override);

            //ASSIGNED TASKS
            $sql = sprintf(CU_SQL_SELECT_ASSIGNED_TASKS, $this->ID);
            $result = $DB->Query($sql);
            if ($result) {
                foreach ($result as $key => $value) {
                        $this->AssignedTasks[] = $value['ID'];
                }
            }
            
            // create module list
            $SQL = CU_SQL_SYSTEM_MODULES;
            $result = $DB->Query($SQL);
            if (is_array($result))
            {
                $count = count($result);
                for ($index = 0; $index < $count; $index++)
                {
                    $record = $result[$index];
                    $public = $record['IsPublic'];
                    $name = $record['Name'];
                    $class = $record['Class'];
                    if ($public == 1)
                    {
                        if ($this->HasModuleObjectAccess($class))
                            $this->ModuleList[$name] = $class;
                    }
                    else if (($public == 0) && ($this->HasModuleObjectAccess($class)))
                        $this->ModuleList[$name] = $class;
                }
            }

            // The user is an administrator if they have write access to the admin module.
            // This code MUST COME LAST to work as the module permissions array must exist.
            $this->IsAdmin = $this->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_WRITE);
        }
    }


    function HasCorrectModuleAccess($has = 0, $needs = 1)
    {
        //if not listed in permissions table, allow.
        if ($has == '')
            $has = $needs;
        return ($has >= $needs) ? TRUE : FALSE;
    }

    function HasCorrectObjectAccess($has = 0, $needs = 1)
    {
        return ($has >= $needs) ? TRUE : FALSE;
    }

    function HasModuleObjectAccess($object)
    {
        return (0 == (int)$this->ModulePermissions[$object]) ? FALSE : TRUE;
    }

    // $item is obsolete now - use HasUserItemAccess to see if a user has permissions on a particular client/project.
    function HasModuleItemAccess($object, $item, $required)
    {
        if ($this->HasModuleObjectAccess($object))
        {
            // If they have the access, allow it through
            $access = $this->ModulePermissions[$object];
            if ($this->HasCorrectModuleAccess($access, $required))
                return true;
        }

        return false;
    }

		function requireHasUserItemAccess($object, $item, $required)
		{
			if ($this->HasUserItemAccess($object, $item, $required))
			{
				return;
			} else {
				$this->ThrowError(2001);
				die();
			}
		}

    function HasUserItemAccess($object, $item, $required)
    {
        if ( $this->HasModuleObjectAccess($object) )
        {
            if ('clients' == $object)
                $permissions = $this->ClientPermissions;
            else
                $permissions = $this->ProjectPermissions;

            // If they have the access, allow it through
            $access = $permissions[$item];
            if ($this->HasCorrectObjectAccess($access, $required))
                return TRUE;
        }

        if ($this->IsAdmin)
            return TRUE;
        else
            return FALSE;
    }

    function GetUserItemAccess($object, $access)
    {
        if ($this->HasModuleObjectAccess($object))
        {
            // get the array of items for the objecta
            $permissions = ('clients' == $object) ? $this->ClientPermissions : $this->ProjectPermissions;
            $global = CU_ACCESS_GLOBALKEY;
            // sort them so we can get -1 first, if its available!
            if ($permissions)
            {
                foreach ($permissions as $key => $value)
                {
                    if ($value >= $access)
                        $return[] = $key;
                }
            }
        }
        else
        {
            // User does not even have access to the object
            // Return single array element of 0 (no item in the DB can have an ID of 0)
            $return = array(0);
        }

        if ($return == NULL)
            $return = '0';
        else
            $return = join(',', $return);

        if ($this->IsAdmin)
            $return = '-1';

        return $return;
    }

    function Name()
    {
        return $this->Firstname . ' ' . $this->Lastname;
    }

    function CreateArray(&$result_first, &$result_override) {

        //create 1d array for first:
        if ($result_first) {
            foreach ($result_first as $key => $value) {
               $first_array[$value['ObjectID']] = $value['AccessID'];
            }
            $module_perms = $first_array;
        }

        if ($result_override) {
            foreach ($result_override as $key => $value) {
               $override_array[$value['ObjectID']] = $value['AccessID'];
            }

            if (is_array($module_perms)) {
                $module_perms = $this->array_merge_preserve_keys($first_array,$override_array);
                ksort($module_perms);
            }
            else {
                $module_perms = $override_array;
            }
        }
         return $module_perms;
     }

    function CreateObjectArray(&$result_first, &$result_override) {

        //create 1d array for first:
        if ($result_first) {
            foreach ($result_first as $key => $value) {
               $first_array[$value['ItemID']] = $value['AccessID'];
            }
            $perms_array = $first_array;
        }

        if ($result_override) {
               foreach ($result_override as $key => $value) {
                   $override_array[$value['ItemID']] = $value['AccessID'];
               }

            if (is_array($perms_array)) {
                $perms_array = $this->array_merge_preserve_keys($first_array, $override_array);
                ksort($perms_array);
            }
            else
                $perms_array = $override_array;
        }
        return $perms_array;
     }

    function array_merge_preserve_keys($arr1,$arr2)
       {
               if(!is_array($arr1))
                       $arr1 = array();
               if(!is_array($arr2))
                       $arr2 = array();
               $keys1 = array_keys($arr1);
               $keys2 = array_keys($arr2);
               $keys  = array_merge($keys1,$keys2);
               $vals1 = array_values($arr1);
               $vals2 = array_values($arr2);
               $vals  = array_merge($vals1,$vals2);
               $ret    = array();

               foreach($keys as $key)
               {
                       list($unused,$val) = each($vals);
                       $ret[$key] = $val;
               }

           return $ret;
       } 


}
 
