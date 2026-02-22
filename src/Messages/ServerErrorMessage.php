<?php
namespace RestRouter\Messages;

//class ServerErrorMessage extends RestResponse {
class ServerErrorMessage {
	var $status, $type, $error;
	var $success = 'false';
	public function __construct($exception, $status = 500){
		if (is_array($exception)) {
			$this->status = $exception['status'] ?? $status;
			$this->error = $exception['error'] ?? 'Server Error';
		} elseif (is_string($exception)) {
			$this->error = $exception;
			$this->status = $status;
		} else {
			$this->status = $status;
			$this->error = 'Server Error';
		}
		$reflect = new \ReflectionClass($this);
		$this->type = $reflect->getShortName();
	}	
	public function __toString(){
		$output['status'] = $this->status;
		$output['type'] = $this->type;
		$output['error'] = $this->error;
		return json_encode($output);

	}
}
