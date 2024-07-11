<?php
namespace RestRouter\Exceptions;

class DBErrorException extends CustomException {
  function __construct($message = 'Database Error', $code=500){
    parent::__construct($message, $code);
  }
}
