<?php
namespace RestRouter;

abstract class RestResponse implements JsonSerializable {
	var $status, $type, $data;

	public function __construct($status=200, $data=[]){
		$this->status = $status;
		$this->data = $data;
	}
	public function jsonSerialize(){
		return $this;
	}
	public function setType($type){
		$this->type = $type;
	}
	public function setStatus($status){
		$this->status = $status;
	}

}


