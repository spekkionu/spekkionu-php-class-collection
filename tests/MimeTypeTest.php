<?php
require_once('PHPUnit/Framework/TestCase.php');
require_once(dirname(dirname(__FILE__)).'/lib/MimeType.php');

class MimeTypeTest extends PHPUnit_Framework_TestCase {

  /**
   * Prepares the environment before running a test.
   */
  protected function setUp(){
    parent::setUp();
    
  }
  
  /**
   * Cleans up the environment after running a test.
   */
  protected function tearDown(){
    
  }
  
  
  public function testGetFileMimeType(){
    $type = MimeType::getFileMimeType( basename(__FILE__));
    $this->assertEquals($type, 'application/x-httpd-php', "Check mimetype of this php file.");
  }
  
  public function testGetMimeTypes(){
    $types = MimeType::getMimeTypes();
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $types, "Should return array of mimetypes");
  }
  
  public function testGetPairs(){
    $types = MimeType::getPairs();
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $types, "Should return associative array of mimetypes.");
  }
  
  public function testGetMimeTypeByExtension(){
    $type = MimeType::getMimeTypeByExtension('txt');
    $this->assertEquals($type, 'text/plain', "Check mimetype of .txt file.");
  }
  
  public function testGetNonExistingMimeType(){
    $type = MimeType::getFileMimeType('fake_file_extension');
    $this->assertEquals($type, 'application/octet-stream', "Check mimetype of unkown file type.");
  }
  
  public function testGetCssClassByMimeType(){
    $type = MimeType::getCssClassByMimeType('application/x-httpd-php');
    $this->assertEquals($type, 'application_x-httpd-php', "Get Classname of this php file");
  }
  
  public function testGetCssClassByExtension(){
    $type = MimeType::getCssClassByExtension('txt');
    $this->assertEquals($type, 'text_plain', "Get classname of text file");
  }
}