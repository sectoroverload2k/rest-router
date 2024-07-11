<?php
namespace RestRouter\Exceptions;

class JWTErrorException extends CustomException {
  function __construct($message='JWT Token Error', $code=400){
    parent::__construct($message, $code);
  }
}
