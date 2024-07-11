<?php
 
/* ------------------------------------------------------------- */
/* URL Router class */
/* ------------------------------------------------------------- */
 
class Router {
  static protected $instance;
  static protected $controller;
  static protected $version;
  static protected $action;
  static protected $params;
  static protected $rules;

	const debug = false;

	private static function __debug($message){
		if(self::debug){
			error_log("Router: " . $message);
		}
	}
 
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

    $ruleItems = explode('/',$rule); self::arrayClean($ruleItems);
    $dataItems = explode('/',$data); self::arrayClean($dataItems);

 
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
		$uri_parts = parse_url($url);

    $items = explode('/',$uri_parts['path']);
		parse_str(@$uri_parts['query'], $params);
 
    // remove empty blocks
    foreach($items as $key => $value) {
      if (strlen($value) == 0) unset($items[$key]);
    }
 
    // extract data
    if (count($items)) {
      self::$version = array_shift($items);
      self::$controller = array_shift($items);
      self::$action = array_shift($items);
      self::$params = array_merge($_REQUEST, $params);
    }
  }
 
  protected function __construct() {
    self::$rules = array();
  }
 
  public static function init($baseurl='') {
		$uri_parts = parse_url($_SERVER['REQUEST_URI']);
		$url = $uri_parts['path'];
		parse_str(@$uri_parts['query'], $params);

#    if($baseurl != ''){
#      $url = preg_replace("/^\/$baseurl/", '', $_SERVER['REQUEST_URI'],1);
#    }
    $isCustom = false;
 
    if (count(self::$rules)) {
      foreach(self::$rules as $ruleKey => $ruleData) {
		
        if('/'.$ruleKey == $url || $ruleKey == $url) {
          self::__debug('Overwrite Rule');
          self::$version = $ruleData['version'];
          self::$controller = $ruleData['controller'];
          self::$action = $ruleData['action'];
          self::$params = ($params) ? array_merge($_REQUEST, $params) : $_REQUEST;
          $isCustom = true;
          break;
        }


        #self::__debug('ruleMatch("'.$ruleKey.'", "'.$url.'")');
        $params = self::ruleMatch($ruleKey,$url);
        self::__debug('Params: '.json_encode($params));

        if ($params) {
          self::$version = $ruleData['version'];
          self::$controller = $ruleData['controller'];
          self::$action = $ruleData['action'];
          self::$params = array_merge($_REQUEST, $params);
          self::__debug('IS CUSTOM: TRUE');
          $isCustom = true;
          break;
        }
      }
    }
 
    if (!$isCustom) self::defaultRoutes($url);
    if (!strlen(self::$controller)) self::$controller = 'home';
    if (!strlen(self::$action)) self::$action = 'index';
  }
 
  public static function addRule($rule, $target) {
    self::$rules[$rule] = $target;
  }

  public static function getController() { return self::$controller; }
  public static function getVersion() { return self::$version; }
  public static function getAction() { return self::$action; }
  public static function getParams() { return self::$params; }
  public static function getParam($id) { return self::$params[$id]; }
}
