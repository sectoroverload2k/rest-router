<?php
namespace RestRouter\Exceptions;

class RateLimitException extends CustomException {
	function __construct($message = 'Rate Limit Exceeded', $code=429){
		parent::__construct($message, $code);
	}
}
