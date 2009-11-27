<?php
require_once('PHPUnit/Framework/TestCase.php');
require_once('../lib/Upload/Image.php');

class UploadImageTest extends PHPUnit_Framework_TestCase {
	
  private $upload;
  
  private $file;
  
  private $uploaded = array();
	
  /**
   * Prepares the environment before running a test.
   */
  protected function setUp(){
    parent::setUp();
    // create an image to test with
    $image = imagecreatetruecolor(500, 500);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 1, 5, 5,  'My Test Image', $text_color);
    imagegif($image, dirname(__FILE__).'/tmp/image.gif');
    $this->file = realpath(dirname(__FILE__).'/tmp/image.gif');
    $this->upload = new Upload_Image($this->file);
  }
  
  /**
   * Cleans up the environment after running a test.
   */
  protected function tearDown(){
    $this->upload = null;
    if(is_file($this->file)) unlink($this->file);
    foreach($this->uploaded as $uploaded){
      if(is_file($uploaded)) unlink($uploaded);
    }
    parent::tearDown();
  }
  
  public function testConstructor(){
  	$isClass = ($this->upload instanceof Upload_Image);
  	
    $this->assertTrue($isClass, 'Is class correct instance');
  }
  
  public function testUpload(){
  	$this->upload->addOutput(dirname(__FILE__).'/tmp', 'upload.gif');
  	$this->upload->overwriteMethod(Upload_Image::OVERWRITE);
  	$file = $this->upload->process();
  	$this->uploaded['upload'] = dirname(__FILE__).'/tmp/'.$file[0]['filename'];
  	$this->assertTrue(is_file($this->uploaded['upload']), 'Checking if uploaded file was found.');
  }
  
  public function testRename(){
  	$this->upload = new Upload_Image($this->file);
  	$this->upload->addOutput(dirname(__FILE__).'/tmp', 'rename.gif');
  	$this->upload->addOutput(dirname(__FILE__).'/tmp', 'rename.gif');
  	$this->upload->overwriteMethod(Upload_Image::UNIQUE);
  	$file = $this->upload->process();
  	$this->uploaded['rename1'] = dirname(__FILE__).'/tmp/'.$file[0]['filename'];
  	$this->uploaded['rename2'] = dirname(__FILE__).'/tmp/'.$file[1]['filename'];
  	$this->assertTrue(is_file($this->uploaded['rename1']) && is_file($this->uploaded['rename2']), 'Checking if uploaded files were found.');
  	$this->assertNotEquals($this->uploaded['rename1'], $this->uploaded['rename2'], 'Filenames should be different.');
  }
  
  public function testResize(){
  	$this->upload = new Upload_Image($this->file);
  	$this->upload->setResizeOption(true, true);
  	$this->upload->addOutput(dirname(__FILE__).'/tmp', 'resize.gif', 100, 100);
  	$this->upload->addOutput(dirname(__FILE__).'/tmp', 'resize2.gif', 100, 50);
  	$this->upload->overwriteMethod(Upload_Image::OVERWRITE);
  	$file = $this->upload->process();
  	$this->uploaded['resize'] = dirname(__FILE__).'/tmp/'.$file[0]['filename'];
  	$this->uploaded['resize2'] = dirname(__FILE__).'/tmp/'.$file[1]['filename'];
  	$this->assertTrue(is_file($this->uploaded['resize']), 'Checking if uploaded file was found.');
  	$this->assertTrue(is_file($this->uploaded['resize2']), 'Checking if uploaded file was found.');
  	$this->assertEquals($file[0]['width'], 100, 'Checking width.');
  	$this->assertEquals($file[0]['height'], 100, 'Checking height.');
  	$this->assertEquals($file[1]['width'], 50, 'Checking width.');
  	$this->assertEquals($file[1]['height'], 50, 'Checking height.');
  	
  }
}