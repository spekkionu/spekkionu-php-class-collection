<?php

require_once(dirname(dirname(__FILE__)).'/lib/StatesOptions.php');

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * StatesOptions test case.
 */
class StatesOptionsTest extends PHPUnit_Framework_TestCase {

  /**
   * @var StatesOptions
   */
  private $StatesOptions;

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
  	StatesOptions::clearCache();
    parent::tearDown();
  }
  
  public function testsetXML(){
  	try{
      $file = realpath(dirname(__FILE__) . '/../lib/Options/states.xml');
      StatesOptions::setXML($file);
  	}catch(Exception $e){
  		$this->file($e->getMessage());
  	}
  }
  
  public function testclearCache(){
    StatesOptions::clearCache();
  }


  /**
   * Tests StatesOptions::getOptions()
   */
  public function testGetOptions(){
    $options = StatesOptions::getOptions();
    $this->assertTrue(strlen($options) > 0, 'Could not load options string.');
  }

  /**
   * Tests StatesOptions::getArray()
   */
  public function testGetArray(){
    $states = StatesOptions::getArray();
    $this->assertArrayHasKey('CA', $states, 'California is missing, states array faile dto load.');
  }

  /**
   * Tests StatesOptions::getPairs()
   */
  public function testGetPairs(){
    $states = StatesOptions::getPairs();
    $this->assertArrayHasKey('CA', $states, 'California is missing, states array faile dto load.');
    
  }

  /**
   * Tests StatesOptions::getState()
   */
  public function testGetState(){
    $state = StatesOptions::getState('CA');
    $this->assertEquals('California', $state, 'Failed to load state California');
    
    // Test failure
    $state = StatesOptions::getState('XX');
    $this->assertFalse($state, 'State should be empty.');
  }

}

