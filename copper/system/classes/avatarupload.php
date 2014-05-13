<?php
/**
 * Avatarupload class
 * $Id $
 */

class AvatarUpload extends Upload
{
	public function __construct()
	{
		parent::__construct(array(
			'image/png'  => 'png',
			'image/jpeg' => 'jpg',
			'image/jpg'  => 'jpg',
			'image/gif'  => 'gif',
		));
	}
	
}