<?php
/*
** This file contains basically a whole lot of utility functions, as well as the main module selector that then directs the application flow.
**
** todo: Move the appropriate ones into different utility classes.
** $Id$
*/


/**
 * Delete a file (and all it's versions)
 * @param   $file     string    The filename
 * @return  bool                indicating success
 */
function cuDeleteFile($file) 
{
    $fname   = $file['FileName'];
    $rname   = $file['RealName'];
    $type    = $file['Type'];
    $size    = $file['Size'];
    $project = $file['ProjectID'];
    $task    = $file['TaskID'];
    $version = $file['Version'];

    // trailing slash - checks for trailing slash, just incase someone didn't put it in
    $trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
    if (($trailer != '\\') && ($trailer != '/')) 
        $filepath = SYS_FILEPATH . '/';
    else 
        $filepath = SYS_FILEPATH;
    //~ trailing slash

    $project_dir = str_pad($project, 7, '0', STR_PAD_LEFT);
    $task_dir    = str_pad($task, 7, '0', STR_PAD_LEFT);
    $filename    = $filepath . $project_dir . '/' . $task_dir . '/' . $rname;

    // File will actually be a dir if file versioning is enabled, or has been in the past.
    // The check for FILE_VERSIONING_ENABLED is omitted here as a "just in case" measure, 
    // since cuDeleteFile() should just get rid of the file regardless of how it's stored.
    if ( is_file( $filename ) )
        return @unlink( $filename );
    else if ( is_dir( $filename ) ) 
        return cuDeleteDir( $filename . '/' );
    else 
        return ( @filetype( $filename ) == false );  // Returns true if file doesn't exist
}

/**
 * Recursively delete a directory. $dir param must have trailing slash.
 * chmod() isn't supposed to work on Windows, but apparently it clears the read-only flag:
 * http://www.php.net/manual/en/function.chmod.php#72419
 * Return true if the dir was deleted, false if it wasn't.
 *
 * @param   $dir     string     The directory to delete
 * @return  bool                indicating success
 */
function cuDeleteDir( $dir ) 
{
    if ( $handle = @opendir( $dir ) ) 
    {
        while ( ( $file = readdir( $handle ) ) !== false ) 
        {
            if ( $file == "." || $file == ".." )
                continue;  // Don't delete current or parent dir just yet.

            if ( is_dir( $dir . $file ) )
            {
                @chmod( $dir . $file, 0777 );
                cuDeleteDir( $dir . $file . '/' );
            }
            else 
            {
                @chmod( $dir . $file, 0777 );
                @unlink( $dir . $file );
            }
        }

        @closedir( $handle );
    }

    return rmdir( $dir );
}

/**
 * This function only deletes the contents of a dir, not the dir itself.
 * To delete a dir, use cuDeleteDir();
 * $Dir param must have trailing slash.
 *
 * @param   $dir     string     The directory to delete
 * @return  <nothing>
 */
function delete_all_from_dir($Dir) 
{
    // delete everything in the directory
    if ($handle = @opendir($Dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file == "." || $file == "..") {
                continue;
            }
            if (is_dir($Dir.$file))    {
                // call self for this directory
                delete_all_from_dir($Dir.$file.'/');
                chmod($Dir.$file, 0777);
                rmdir($Dir.$file); //remove this directory
            }
            else {
                chmod($Dir.$file,0777);
                unlink($Dir.$file); // remove this file
            }
        }
    }
    @closedir($handle);
}

/**
 * Create a pagination interface. 
 * @param   $num_records    int     The number of records
 * @param   $limit          int     The limit to paginate to
 * @param   $url            string  The url to use as a base
 * @param   $tmpl           array   Default string values
 * @return                  array   Containing the various parts of the pagination stuff
 */
function cuPaginate($num_records, $limit, $url, $offset, &$tmpl)
{
    $tmpl['PREV'] = $tmpl['NEXT'] = $tmpl['ALL'] = $tmpl['PAGES'] = '';

    // Prev link if needed.
    if (intval($offset) > 0) 
    {
        $prevoffset = $offset - $limit;
        $tmpl['PREV'] = '<a class="linkon" href="' . $url . '&amp;start=' . $prevoffset . '">' . MSG_PREV . '</a> | ';
    }

    // Page Numbers
    $num_pages = $num_records / $limit;
    if ($num_pages > 1) 
    {
        for ($i=0,$j=1; $i < $num_pages; $i++,$j++) 
        {
            $start = $limit * $i;
            // PHP converts $offset to int and gets zero when comparing to $start, so don't let it.
            $pageNum = ($offset != 'all' && $start == $offset) ? "<strong>$j</strong>" : $j;
            $tmpl['PAGES'] .= '<a class="linkon" href="' . $url . '&amp;start=' . $start . '">' . $pageNum . '</a> ';
        }

        // Show All
        $msg = ($offset == 'all') ? '<strong>'.MSG_ALL.'</strong>' : MSG_ALL;
        $tmpl['ALL'] = ' | <a class="linkon" href="'.$url.'&amp;start=all">'.$msg.'</a>';
    }

    // Next link if needed.
    if (!(($offset + $limit) >= $num_records)) 
    {
        $newoffset = $offset + $limit;
        $tmpl['NEXT'] = '| <a class="linkon" href="'.$url.'&amp;start='.$newoffset.'">'.MSG_NEXT.'</a>';
    }

    return $tmpl;
}
