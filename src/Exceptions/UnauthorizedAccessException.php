<?php
namespace RestRouter\Exceptions;

class UnauthorizedAccessException extends CustomException { 
	function __construct($message='Unauthorized Access', $code=403){
		parent::__construct($message, $code);
	}
}
