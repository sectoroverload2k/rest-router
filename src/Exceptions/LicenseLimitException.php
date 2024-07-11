<?php
namespace RestRouter\Exceptions;

class LicenseLimitException extends CustomException {
  function __construct($message = 'License Limit', $code=403){
    parent::__construct($message, $code);
  }
}
