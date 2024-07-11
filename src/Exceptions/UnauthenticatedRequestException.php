<?php
namespace RestRouter;

class UnauthenticatedRequestException extends CustomException {
	function __construct($message='Unauthenicated Access', $code=401){
		parent::__construct($message, $code);
	}
}
