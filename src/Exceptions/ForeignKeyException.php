<?php
namespace RestRouter\Exceptions;

class ForeignKeyException extends CustomException {
	function __construct($message = 'Foreign Key Constraint', $code=400){
		parent::__construct($message, $code);
	}
}
