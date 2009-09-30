<?php
/**
 * States Options Class
 * @author Jonathan Bernardi
 * @copyright 2009 spekkionu <spekkionu@spekkionu.com>
 * @package Options
 * @uses SimpleXML
 */
class StatesOptions {
	
	private static $file = null;
	
	private static $states = null;
	
	/**
	 * Constructor is static for a static class
	 * @return unknown_type
	 */
	private function __construct(){}

	/**
	 * Sets the xml file to use to load states
	 * @param string $file
	 * @return void
	 */
	public static function setXML($file){
		if(!is_file($file)) throw new Exception('States XML file does not exist.');
		self::$file = realpath($file);
		self::$states = null;
	}
	
	/**
	 * Loads the states from the xml file.
	 * @return void
	 */
	private static function loadStates(){
		if(is_null(self::$file)) self::$file = dirname(__FILE__) . '/Options/states.xml';
	  if(!is_file(self::$file)) throw new Exception('States XML file does not exist.');
    // Load xml file
	  $xml = simplexml_load_file(self::$file);
	  $states = array();
	  // loop through states
	  foreach($xml as $state){
	  	$states[(string) $state->code] = array(
	  	  'code' => (string) $state->code,
	  	  'name' => (string) $state->name,
	  	  'isus' => ($state->isus == 'yes') ? true : false
	  	);
	  }
	  // Cache states
	  self::$states = $states;
	  // Clear xml instance
	  unset($xml, $states, $state);
	}
	
	/**
	 * Clears the states from the cache.
	 * @return void
	 */
	public static function clearCache(){
		self::$states = null;
	}
	
	/**
	 * Returns string with <option> tags
	 * @return string
	 */
	public static function getOptions($selected = null, $abbr_as_values = true, $us_only = true){
		// Load States if they are not yet loaded.
    if(is_null(self::$states)) self::loadStates();
    $string = '';
    foreach(self::$states as $state){
    	if(!$us_only || $state['isus']){
	    	$value = ($abbr_as_values) ? $state['code'] : $state['name'];
	    	$seltext = (!is_null($selected) && $selected == $value) ? 'selected="selected"' : '';
	    	$string .= '<option value="'
	    	        .  htmlentities($value).'" '
	    	        . $seltext . '>'
	    	        . htmlentities($state['name'])
	    	        . '</option>';
    	}
    }
    unset($value, $seltext, $state);
    return $string;
	}
	
	/**
	 * Returns array of states
	 * @param bool $us_only
	 * @return array
	 */
	public static function getArray($us_only = true){
		// Load States if they are not yet loaded.
		if(is_null(self::$states)) self::loadStates();
		$states = self::$states;
		foreach($states as $key=>$value){
			if($us_only && !$value['isus']) unset($states[$key]);
		}
		return $states;
	}
	
	/**
	 * Returns states as key=>value pair array
	 * @param bool $us_only
	 * @return array
	 */
	public static function getPairs($us_only = true){
		// Load States if they are not yet loaded.
    if(is_null(self::$states)) self::loadStates();
    $states = array();
    foreach(self::$states as $key=>$state){
    	if(!$us_only || $state['isus']){
    		$states[$state['code']] = $state['name'];
    	}
    }
    return $states;
	}
	
	/**
	 * Returns a single state by abbreviation
	 * @param string $state
	 * @param bool $name_only
	 * @return string|array
	 */
	public static function getState($state, $name_only = true){
		// Load States if they are not yet loaded.
    if(is_null(self::$states)) self::loadStates();
    if(array_key_exists($state, self::$states)){
    	return ($name_only) ? self::$states[$state]['name'] : self::$states[$state];
    }else{
    	return false;
    }
	}
	
}