<?php
/**
 * MimeType Parsing Class
 * @author Jonathan Bernardi
 * @copyright 2009 spekkionu <spekkionu@spekkionu.com>
 * @package MimeTypes
 * @license MIT License
 */
class MimeType {
	
  private static $file = null;
  
  private static $data = null;
  
  /**
   * Sets the xml file to parse
   * @param string $file
   * @return void
   */
  public static function setXmlFile($file){
    if(!is_file($file)) throw new Exception('MimeType XML file does not exist.');
    self::$file = realpath($file);
  }
  
  /**
   * Loads and parses the xml file
   * @return void
   */
  private static function loadXmlFile(){
    if(is_null(self::$file)){
      self::setXmlFile(dirname(__FILE__).'/MimeType/mimetypes.xml');
    }
    $xml = simplexml_load_file(self::$file);
    $mimetypes = array();
    // loop through data
    foreach($xml as $type){
      $mimetypes[(string) $type->extension] = array(
        'extension' => (string) $type->extension,
        'mimetype' => (string) $type->mime
      );
    }
    // Cache data
    self::$data = $mimetypes;
    // Clear xml instance
    unset($xml, $mimetypes, $type);
  }
  
  /**
   * Returns mime-type data in an array
   * @return array
   */
  public static function getMimeTypes(){
    // Load Data
    self::loadXmlFile();
    return self::$data;
  }
  
  /**
   * Returns mime-type data in an array with extensions as keys and mimetypes as values
   * @return array
   */
  public static function getPairs(){
    // Load Data
    self::loadXmlFile();
    $array = array();
    foreach(self::$data as $value){
      $array[$value['extension']] = $value['mimetype'];
    }
    return $array;
  }
  
  /**
   * Returns the mime-type of the given file
   * @param string $file Path to a file or just the filename
   * @return string
   */
  public static function getFileMimeType($file){
    $file = basename($file);
    if(stristr($file, '.') === false){
      // return application/octet-stream for files without an extension
      return 'application/octet-stream';
    }
    $filename = explode('.', $file);
    $ext = array_pop($filename);
    return self::getMimeTypeByExtension($ext);
  }
  
  /**
   * Returns the mime-type for a given extension
   * @param string $extension
   * @return string
   */
  public static function getMimeTypeByExtension($extension){
    $types = self::getPairs();
    return isset($types[$extension]) ? $types[$extension] : 'application/octet-stream';
  }
  
  /**
   * Returns a css class for the given mime-type
   * Will be the mime-type with underscore instead of /
   * @param $mimetype
   * @return string
   */
  public static function getCssClassByMimeType($mimetype){
    $class = str_replace('/', '_', $mimetype);
    return preg_replace("/[^a-z0-9-_]/i", "-", $class);
  }
  
  /**
   * Returns a css class for the given extension
   * Will be the mime-type with underscore instead of /
   * @param $extension
   * @return string
   */
  public static function getCssClassByExtension($extension){
    $type = self::getMimeTypeByExtension($extension);
    return self::getCssClassByMimeType($type);
  }
}