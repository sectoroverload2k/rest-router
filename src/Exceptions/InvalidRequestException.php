<?php
namespace RestRouter;

class InvalidRequestException extends CustomException { 
	function __construct($message='Bad Request', $code=400){
		parent::__construct($message, $code);
	}
}
