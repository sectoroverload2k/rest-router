<?php
namespace RestRouter;

class EmptyResponseException extends CustomException {
	function __construct($message='No Content', $code=200){
		parent::__construct($message, $code);
	}
}
