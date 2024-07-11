<?php
namespace RestRouter\Exceptions;

class EmptyResponseException extends CustomException {
	function __construct($message='No Content', $code=200){
		parent::__construct($message, $code);
	}
}
