<?php
namespace RestRouter\Messages;

//class ServerErrorMessage extends RestResponse {
class ServerErrorMessage {
	var $status, $type, $error;
	var $success = 'false';
	public function __construct($exception){
		$this->status = $exception['status']; //->status;
		$this->error = $exception['error'];
		$this->type = get_class($this);
	}	
	public function __toString(){
		$output['status'] = $this->status;
		$output['type'] = $this->type;
		$output['error'] = $this->error;
		return json_encode($output);

	}
}
