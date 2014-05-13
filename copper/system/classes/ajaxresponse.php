<?php
/**
 * Handles creating ajax responses, creating them in the standard format.
 *
 */
class AjaxResponse
{
	private $data = array();
	private $response_code;
	private $success;
	private $message;
	private $callback = null;
	
	/* By default, we have a successful operation, with no data to return. */
	function __construct($success = TRUE, $message = null, $response_code = null) 
	{

		header('Content-type: application/json;charset=utf-8');
		if ($response_code == null)
		{
			// guess a nice code.
			$this->response_code = ($success) ? 200 : 400;
		} else 
		{
			$this->response_code = $response_code;
			
		}
		$this->success = $success;
		$this->message = $message;
	}
	
	public function __set($var, $val)
	{
		switch( $var ) {
			case 'response_code':
			case 'message':
			case 'data':
			case 'callback':
				$this->$var = $val;
				break;
			default:
				$this->add_to_data($var, $val);
				break;
		}
	}
	
	public function add_to_data($key, $val)
	{
		$this->data[$key] = $val;
	}


	public function out()
	{
		$ret_array = array(
			'response_code' => $this->response_code,
			'message' => $this->message,
			'data' => $this->data,
		);

		// if some callback js has been provided, include that too.
		if ($this->callback != null)
		{
			$ret_array['callback'] = $this->callback;
		}
		
		$this->set_header_from_code();

		echo json_encode($ret_array);
		die();
	}
	
	private function set_header_from_code()
	{
		// for more see here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
		switch($this->response_code)
		{
			case 200:
				$str = 'Success';
				break;
			case 302:
				$str = 'Found';
				break;
			case 400:
				$str = "Bad Request";
				break;
			case 401:
				$str = "Unauthorized";
				break;
			case 403:
				$str = "Forbidden";
				break;
			case 404:
				$str = "Not Found";
				break;
			default:
				$this->response_code = 500;
				$str = "Internal server error";
		}
		
		header("HTTP/1.0 " . $this->response_code . " " . $str, TRUE, $this->response_code);
	}

}
