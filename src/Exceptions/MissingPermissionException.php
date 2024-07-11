<?php
namespace RestRouter\Exceptions;

class MissingPermissionException extends CustomException {
  function __construct($message='Forbidden', $code=403){
    parent::__construct($message, $code);
  }
}
