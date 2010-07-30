<?php
require_once('PHPUnit/Framework/TestCase.php');
require_once(dirname(dirname(__FILE__)).'/lib/Cache/Backend/Wincache.php');

class WincacheImageTest extends PHPUnit_Framework_TestCase {

  private $frontendOptions = array('automatic_serialization' => true);
   
  private $cache = null;
  /**
   * Prepares the environment before running a test.
   */
  protected function setUp(){
    parent::setUp();
    // Add library folder to include path
    set_include_path(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib' . PATH_SEPARATOR . get_include_path());
    // Setup Zend_Loader_Autoloader
    require_once('Zend/Loader/Autoloader.php');
    $autoloader = Zend_Loader_Autoloader::getInstance();
    // Init instance
    $this->cache = Zend_Cache::factory('Core', 'Cache_Backend_Wincache', $this->frontendOptions, array(), false, true);
  }
  
  /**
   * Cleans up the environment after running a test.
   */
  protected function tearDown(){
    // Clear All Cache
    $this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    // Remove Instance
    $this->cache = null;
  }
  
  public function testConstructor(){
  	$cache = Zend_Cache::factory('Core', 'Cache_Backend_Wincache', $this->frontendOptions, array(), false, true);
    $this->assertType('Zend_Cache_Core', $cache, "Not the expected type of Zend_Cache_Core");
    unset($cache);
  }
  
  public function testSave(){
    $string = "teststring";
    $id = "testsave";
    $this->assertTrue($this->cache->save($string, $id), "Failed to save data");
    $this->cache->remove($id);
  }
  
  public function testLoad(){
    $string = "teststring";
    $id = "testsave";
    $this->cache->save($string, $id);
    $data = $this->cache->load($id);
    $this->assertEquals($data, $string, "Failed to load saved data");
    $this->cache->remove($id);
  }
  
  public function testRemove(){
    $string = "teststring";
    $id = "testsave";
    $this->cache->save($string, $id);
    $this->assertTrue($this->cache->remove($id), "Failed to remove data");
  }
  
  
  public function testBadLoad(){
    $id = "testsave";
    // Make sure id doesn't exist
    $this->cache->remove($id);
    // try to load the data
    $this->assertFalse($this->cache->load($id), "Remove of non-existing key should have failed");
  }
  
  public function testCacheTest(){
    $string = "teststring";
    $id = "testsave";
    $this->cache->save($string, $id);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $this->cache->test($id), "Test of save failed");
    $this->cache->remove($id);
    $this->assertFalse($this->cache->test($id), "Test of cache test failed");
  }
  
  public function testClean(){
    $this->assertTrue($this->cache->clean(Zend_Cache::CLEANING_MODE_ALL), "Failed to clean cache");
  }
  
  
  public function testFillingPercentage(){
    $percentage = $this->cache->getFillingPercentage();
    $this->assertGreaterThanOrEqual(0, $percentage, "Percentage was too small");
    $this->assertLessThanOrEqual(100, $percentage, "Percentage was too large");
  }
  
  public function testGetIds(){
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $this->cache->getIds(), "Return of existing keys failed");
  }
  
  public function testGetMetadata(){
    $string = "teststring";
    $id = "testsave";
    $this->cache->save($string, $id);
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $this->cache->getMetadatas($id), "return of metadata failed");
    $this->cache->remove($id);
  }
  
  public function testTouch(){
    $string = "teststring";
    $id = "testsave";
    $this->cache->save($string, $id);
    $this->assertTrue($this->cache->touch($id, 100), "Failed to refresh cache");
    $this->cache->remove($id);
  }
  
  
}