<?php
namespace RestRouter;

class ServerObjectResponse extends RestResponse {
	var $status, $success, $data, $type;

	public function __construct($status=200,$data=[]){
		parent::__construct($status,$data);
		$this->success = 'true';
	}


	public function __toString(){
		$output['status'] = $this->status;
		$output['success'] = $this->success;
		$output['type'] = $this->type;
		$output['data'] = $this->data;
		return json_encode($output);
	}

	
}
