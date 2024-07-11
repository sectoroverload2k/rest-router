<?php
namespace RestRouter;

class NotImplementedException extends CustomException {
  function __construct($message='Not Implemented', $code=501){
    parent::__construct($message, $code);
  }
}
