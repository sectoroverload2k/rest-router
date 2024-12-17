<?php
namespace RestRouter\Responses;

class ServerListResponse extends RestResponse {
	var $success;
	var $status, $data, $type, $type_of, $count;
	
	public function setTypeOf($type){
		$this->type_of = $type;
	}
	public function __construct($status=200,$data=[]){
		parent::__construct($status,$data);
		$this->count = count($data);
		$this->success = 'true';
	}
	public function __toString(){
		$output['status'] = $this->status;
		$output['success'] = $this->success;
		$output['type'] = $this->type;
		$output['type_of'] = $this->type_of;
		$output['count'] = count($this->data);
		$output['data'] = $this->data;
		return json_encode($output);
	}

}
