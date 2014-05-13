<?php
/**
 * Upload class
 * $Id$
 */

abstract class Upload
{
	// ATTENTION! 
	// You need to provide this array in subclasses, for the mimetype check to work.
	protected $valid_mimetypes = array();

	// if you want files to be uploaded to a specifc directory, then set this:
	// protected $path_suffix = '/avatar';

	const ERR_NO_FILEUPLOAD								= -1;
	const ERR_BAD_FILETYPE								= -2;
	const ERR_COULDNT_DETERMINE_FILETYPE	= -3;
	const ERR_UPLOAD_TOO_LARGE						= -4;
	const ERR_FOLDER_NOT_FOUND						= -5;
	// in bytes please. No images larger that 16 mb.
	const MAX_FILE_UPLOAD_SIZE = 16777216;
	
	// Folder relative to copper root
	const FILE_STORAGE_PATH =  '/uploads';
	
	public function __construct($valid_mimetypes)
	{
		$this->valid_mimetypes = $valid_mimetypes;
	}
	
	public function create_from_upload($field)
	{
		if ( empty( Request::$FILES ) )
		{
			return Upload::ERR_NO_FILEUPLOAD;
		}
		
		$file_upload = Request::files($field);

		$mime = null;
		// this breaks in copper.
		if (class_exists('finfo', FALSE))
		{
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file_upload['tmp_name']);
		} else if (function_exists('mime_content_type')) 
		{
			$mime = mime_content_type($file_upload['tmp_name']);
		} else if ( isset( $file_upload['type'] ) )
		{
			// oh well, we'll just have to trust the browser mime-type
			$mime = $file_upload['type'];
			if ($mime == 'application/octet-stream')
			{
				// huh. yeah right.
				$mime = null;
			}
		} 

		if ($mime == null)
		{
			// oh well, we couldn't detect it, so we'll just have to trust the user. oh shit.
			$ext = strtolower( substr( strrchr( $file_upload['name'], '.' ), 1 ) );
			$mime = array_search( $ext, $this->valid_mimetypes );
		}

		if ( ! in_array($mime, array_keys( $this->valid_mimetypes ) ) )
		{
			return Upload::ERR_BAD_FILETYPE;
		}
		
		if ( $file_upload['size'] > Upload::MAX_FILE_UPLOAD_SIZE )
		{
			return Upload::ERR_IMAGE_TOO_LARGE;
		}
		
		// hokay. no we are all good methinks.
		$path = self::get_full_storage_path();

		if ( ! is_dir( $path ) )
		{
			mkdir($path, 0755, true);
		}
		
		if ( ! is_dir( $path) )
		{
			// we tried, and we failed. 
			return Upload::ERR_FOLDER_NOT_FOUND;
		}

		$md5 = md5_file($file_upload['tmp_name']);
		// we give it an extension so we can find it in a browser still.
		$dest_file = $md5 . '.' . $this->valid_mimetypes[$mime];
		move_uploaded_file($file_upload['tmp_name'], $path . '/' . $dest_file);

		return array(
			'path'			=> $path,
			'filename' => $dest_file,
			'fullname' => $path . '/' . $dest_file
		);
	}
	
	public function create_from_filesystem($file)
	{
		if ( ! is_readable($file) )
		{
			return Upload::ERR_NO_FILEUPLOAD;
		}
		
		$mime = null;
		// this breaks in copper.
		if (class_exists('finfo', FALSE))
		{
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($file);
		} else if (function_exists('mime_content_type')) 
		{
			$mime = mime_content_type($file);
		}
		
		if ( filesize($file) > Upload::MAX_FILE_UPLOAD_SIZE )
		{
			return Upload::ERR_IMAGE_TOO_LARGE;
		}
		
		if ($mime == null)
		{
			// oh well, we couldn't detect it, so we'll just have to trust the existing extension
			$ext = strtolower( substr( strrchr( $file, '.' ), 1 ) );
			$mime = array_search( $ext, $this->valid_mimetypes );
		}

		if ( ! in_array($mime, array_keys( $this->valid_mimetypes ) ) )
		{
			return Upload::ERR_BAD_FILETYPE;
		}
		
		// hokay. no we are all good methinks.
		$path = self::get_full_storage_path();

		if ( ! is_dir( $path ) )
		{
			mkdir($path, 0755, true);
		}
		
		if ( ! is_dir( $path) )
		{
			// we tried, and we failed. 
			return Upload::ERR_FOLDER_NOT_FOUND;
		}

		$md5 = md5_file($file);
		// we give it an extension so we can find it in a browser still.
		$dest_file = $md5 . '.' . $this->valid_mimetypes[$mime];
		rename($file, $path . '/' . $dest_file);

		return array(
			'path'			=> $path,
			'filename'	=> $dest_file,
			'fullname'	=> $path . '/' . $dest_file
		);
	}
	
	public static function get_full_storage_path()
	{
		if (IS_HOSTED)
		{
			return Utils::get_hosted_path() . self::FILE_STORAGE_PATH;
		} else {
			return realpath(CU_SYSTEM_PATH . '../' . self::FILE_STORAGE_PATH);
		}
	}
	
	public static function get_url($filename)
	{
		return './' . self::FILE_STORAGE_PATH . '/' . $filename;
	}
	
	public static function get_upload_error($flag)
	{
		switch($flag) {
			case Upload::ERR_NO_FILEUPLOAD:
				$msg = "No file to upload";
				break;
			case Upload::ERR_BAD_FILETYPE:
				$msg = "Invalid filetype";
				break;
			case Upload::ERR_COULDNT_DETERMINE_FILETYPE:
				$msg = "Could not determine filetype";
				break;
			case Upload::ERR_UPLOAD_TOO_LARGE:
				$msg = "Upload too large.";
				break;
			case Upload::ERR_FOLDER_NOT_FOUND:
				$msg = "Upload folder not found";
			default:
				$msg = "Unknown error: $image";
		}

		return $msg;
	}


}

