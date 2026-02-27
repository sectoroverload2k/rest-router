<?php
namespace RestRouter;

use RestRouter\Messages\ServerErrorMessage;

class RestUtils
{


	public static function processRequest()
	{
		// get our verb
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		$return_obj		= new RestRequest();
		// we'll store our data here
		$data			= array();

		switch ($request_method)
		{
			// gets are easy...
			case 'get':
				$data = $_GET;
				break;
			// so are posts
			case 'post':
				$data = $_POST;
				#pre($data);exit;

				$post_data = HTTPInputData::process();
#				$data = $post_data['variables'];
				$data = array_merge($_POST, @$post_data['variables']);
				break;
			// here's the tricky bit...
			case 'put':
			case 'patch':
				// basically, we read a string from PHP's special input location,
				// and then parse it out into an array via parse_str... per the PHP docs:
				// Parses str  as if it were the query string passed via a URL and sets
				// variables in the current scope.
				$put_data = HTTPInputData::process();
				$data = $put_data['variables'];
				break;

			case 'delete':
				$delete_data = HTTPInputData::process();
				$data = $delete_data['variables'];
#				parse_str(file_get_contents('php://input'), $delete_vars);
#				$data = $delete_vars;
				break;
		}

		error_log('RestRequest->data: ' . json_encode($data));

		// store the method
		$return_obj->setMethod($request_method);

		// set the raw data, so we can access it if needed (there may be
		// other pieces to your requests)
		$return_obj->setRequestVars($data);

		if(isset($data['data']))
		{
			// translate the JSON to an Object for use however you want
			// Check if it's a string (needs decoding) or already decoded (array/object)
			if (is_string($data['data'])) {
				$return_obj->setData(json_decode($data['data']));
			} else {
				// Already decoded by HTTPInputData::process()
				$return_obj->setData($data['data']);
			}
		}
		return $return_obj;
	}

	public function appendJsonStatus(&$response, $status){
		if(!isset($response['status'])){
			$response['status'] = $status;
		}
		if(!isset($response['message'])){
			$response['message'] = RestUtils::getStatusCodeMessage($status);
		}
		#return $response;
	}

  public static function sendJsonError($status, $body = null, $content_type = 'application/json')
	{
		$response = new ServerErrorMessage($body, $status);
		if(gettype($body)=='object'){
			$response->setType(get_class($body));
		}

    RestUtils::sendResponse($status, $response, $content_type);
  } 

  /**
   * Send an HTTP redirect
   *
   * @param string $location URL to redirect to
   * @param int $status Redirect status code (301 = permanent, 302 = temporary)
   */
  public static function sendRedirect($location, $status = 302)
  {
    if (!in_array($status, [301, 302, 303, 307, 308])) {
      $status = 302; // Default to temporary redirect
    }

    header('Location: ' . $location, true, $status);
    RestUtils::sendResponse($status, '', 'text/html');
  }

  public static function sendJsonResponse($status = 200, $body = [], $content_type = 'application/json')
  {

		//
		// ServerListResponse
		//
		$response; // = [];
		if(gettype($body) == 'object'){
			preg_match('/([A-Z][a-z]+)*/', get_class($body), $matches);
			if($matches){
				if(array_pop($matches) == 'List'){
					$orig_class = preg_replace('/List$/','',get_class($body), 1);
					$response = new Responses\ServerListResponse($status, $body);
					$response->setType($matches[0]);
					$response->setTypeOf($orig_class);
				} else {
					$response = new Responses\ServerObjectResponse($status, $body);
					$response->setType($matches[0]);
				}
			}
		} else {
			$response = new Responses\ServerObjectResponse($status,$body);
			$response->setType(gettype($body));
		}
    RestUtils::sendResponse($status, $response, $content_type);
  }

	public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: ' . $content_type);

		// Redirect status codes should not have a body
		// Just send headers and exit (prevents default nginx HTML from being shown)
		if (in_array($status, [301, 302, 303, 307, 308])) {
			exit;
		}

		// pages with body are easy
		if($body != '')
		{
			// send the body
			echo $body;
			exit;
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			// servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templatized in a real-world solution
			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
								<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>
							</head>
							<body>
								<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>
								<p>' . $message . '</p>
								<hr />
								<address>' . $signature . '</address>
							</body>
						</html>';

			echo $body;
			error_log("RESPONSE: $body");
			exit;
		}
	}


	public static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}


