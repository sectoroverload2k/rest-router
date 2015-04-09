<?php
 
/* ------------------------------------------------------------- */
/* URL Router class */
/* ------------------------------------------------------------- */
 
class Router {
  static protected $instance;
  static protected $version;
  static protected $controller;
  static protected $action;
  static protected $params;
  static protected $rules;
 
  public static function getInstance() {
    if (isset(self::$instance) and (self::$instance instanceof self)) {
      return self::$instance;
    } else {
      self::$instance = new self();
      return self::$instance;
    }
  }
 
  private static function arrayClean($array) {
    foreach($array as $key => $value) {
      if (strlen($value) == 0) unset($array[$key]);
    }  
  }
 
  private static function ruleMatch($rule, $data) {    
	if($rule == $data) return array();

    $ruleItems = explode('/',$rule); self::arrayClean(&$ruleItems);
    $dataItems = explode('/',$data); self::arrayClean(&$dataItems);

 
    if (count($ruleItems) == count($dataItems)) {
      $result = array();
 
      foreach($ruleItems as $ruleKey => $ruleValue) {
        if (preg_match('/^:[\w]{1,}$/',$ruleValue)) {
          $ruleValue = substr($ruleValue,1);
          $result[$ruleValue] = $dataItems[$ruleKey];
        }
        else {
          if (strcmp($ruleValue,$dataItems[$ruleKey]) != 0) {
            return false;
          }
        }
      }
 
      if (count($result) > 0) return $result;
      unset($result);
    }
    return false;
  }
 
  private static function defaultRoutes($url) {
    // process default routes
    $items = explode('/',$url);
	#print_r($items);
 
    // remove empty blocks
    foreach($items as $key => $value) {
      if (strlen($value) == 0) unset($items[$key]);
    }
 
    // extract data
    if (count($items)) {
      self::$version = array_shift($items);
      self::$controller = array_shift($items);
      self::$action = array_shift($items);
      self::$params = $items;
    }
  }
 
  protected function __construct() {
    self::$rules = array();
  }
 
  public static function init($baseurl='') {
    $url = $_SERVER['REQUEST_URI'];
#    if($baseurl != ''){
#      $url = preg_replace("/^\/$baseurl/", '', $_SERVER['REQUEST_URI'],1);
#    }
    $isCustom = false;
 
    if (count(self::$rules)) {
      foreach(self::$rules as $ruleKey => $ruleData) {
		
		if('/'.self::$version.$ruleKey == $url || $ruleKey == $url) {
			#error_log('Overwrite Rule');
            #self::$version = $ruleData['version'];
			self::$controller = $ruleData['controller'];
			self::$action = $ruleData['action'];
			self::$params = $_REQUEST;
			$isCustom = true;
			break;
		}

		error_log('ruleMatch("'.$ruleKey.'", "'.$url.'")');
        $params = self::ruleMatch($ruleKey,$url);
		error_log('Params: '.json_encode($params));

        if ($params) {
          #self::$version = $ruleData['version'];
          self::$controller = $ruleData['controller'];
          self::$action = $ruleData['action'];
          self::$params = $params;
          $isCustom = true;
          break;
        }
      }
    }
 
    if (!$isCustom) self::defaultRoutes($url);
    if (!strlen(self::$version)) self::$version = 'v1'; 
    if (!strlen(self::$controller)) self::$controller = 'home';
    if (!strlen(self::$action)) self::$action = 'index';
  }
 
  public static function addRule($rule, $target) {
    self::$rules[$rule] = $target;
  }

  public static function getVersion() { return self::$version; } 
  public static function getController() { return self::$controller; }
  public static function getAction() { return self::$action; }
  public static function getParams() { return self::$params; }
  public static function getParam($id) { return self::$params[$id]; }
}
