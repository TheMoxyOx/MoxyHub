<?
include('functions.php');
include('common.php');

if ($_GET['license_name'] == "" || $_GET['license_company'] == "" || 
    $_GET['db_username'] == '' || $_GET['db_password'] == '')
{
    echo "NodensError: Missing Fields in coppersetup";
    die();
}
else
{
    $data['date'] = date('Y-m-d');
    $data['name'] = $_GET['license_name'];
    $data['company'] = $_GET['license_company'];
    $data['code'] = (isset($_GET['license_code'])) ? $_GET['license_code'] : LICENSE_CODE; 
    $data['hostname'] = 'localhost';
        if (is_dir("/home/virtual/copperhub.com"))
       $data['database'] = 'copperhub_com_-_'.$_GET['license_name'];
        else
           $data['database'] = 'copperhq_com_-_'.$_GET['license_name'];
    $data['username'] = $_GET['db_username'];
    $data['password'] = $_GET['db_password'];
    $data['fromname'] = "Copper Generated Mail";
    $data['fromaddr'] = "noreply@copperproject.com";
        if (is_dir("/home/virtual/copperhub.com"))
          $data['filepath'] = '/home/copperhub/files/'.$_GET['license_name'].'/';
        else
           $data['filepath'] = '/copperstuff/files/'.$_GET['license_name'].'/';
    $data['servernamevar'] = 'HTTP_HOST';
    $data['scriptnamevar'] = 'REQUEST_URI';

    // Create the config.php from the template.
    if (is_readable('config_local.php.template'))
    {
        $file = file_get_contents('config_local.php.template');
        foreach ($data as $k => $v)
            $file = str_replace('{'.$k.'}', $v, $file);
        $result = @file_put_contents('../config_local.php', $file);
        if (!$result)
        {
            echo "NodensError: Could not write config_local.php file.";
            die();
        }
    }
    else
    {
        echo "NodensError: Could not read config_local.php.template file.";
        die();
    }

    $count = $db->ExecuteScalar("SELECT COUNT(*) AS Count FROM information_schema.tables WHERE table_schema = '{$data['database']}'");
    if ((int)$count > 0)
        upgrade(); // Existing install, just do the upgrade.
    else
        install(); // Create the tables in the database.

    echo "NodensSuccess: Copper Installation Successful!" ;
}
 
