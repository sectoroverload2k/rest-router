<?php
namespace RestRouter;

class ServerErrorResponse extends RestResponse {
	var $status, $success, $error, $type;
	public function __construct($status=500,$message='Server Error'){
		$this->status = $status;
		$this->error = new Messages\ServerErrorMessage($message, $status);
	}

}
