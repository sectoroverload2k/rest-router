<?php
namespace RestRouter\Exceptions;

class NotFoundException extends CustomException {
  function __construct($message='Not Found', $code=404){
    parent::__construct($message, $code);
  }
}
