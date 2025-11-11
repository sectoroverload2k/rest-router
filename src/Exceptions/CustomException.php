<?php
namespace RestRouter\Exceptions;

use RestRouter\RestUtils;
use RestRouter\Interfaces\IException;

abstract class CustomException extends \Exception implements IException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $type;                              // Changes by type
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    // $file and $line are inherited from parent Exception class
    private   $trace;                             // Unknown

    public function __construct($message = "", $code = 0)
    {
				error_log(sprintf('EXCEPTION (%d): %s', $code, $message));
				$reflect = new \ReflectionClass($this);
				$this->type = $reflect->getShortName();
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }
   
    public function __toString()
    {
			$response['status'] = $this->code;
			$response['success'] = false;
    	$response['error'] = array('code' => $this->code, 'message' => $this->message, 'type' => $this->type);

      RestUtils::sendJsonError($this->code,$response, 'application/json');

    }
}
