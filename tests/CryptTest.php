<?php
require_once('PHPUnit/Framework/TestCase.php');
require_once('../lib/Crypt.php');
/**
 * Crypt test case.
 */
class CryptTest extends PHPUnit_Framework_TestCase {

	/**
	 * The encryption key
	 * @var string
	 */
	const KEY = 'fdJTHvhtgyfjcfdbgfdJUGFbfdsgGFDjuhNMfdgFDhgfnvfnhgKhgfHdsvbFDuHGmVCbFDShghgfjnm';
	
	const DATA = 'This is the test string to be encrypted.';
	
  /**
   * @var Crypt
   */
  private $crypt;

  /**
   * Prepares the environment before running a test.
   */
  protected function setUp(){
    parent::setUp();
    $this->crypt = new Crypt(self::KEY);
    
  }

  /**
   * Cleans up the environment after running a test.
   */
  protected function tearDown(){
    $this->crypt = null;
    parent::tearDown();
  }

  /**
   * Tests Crypt->__construct()
   */
  public function test__construct(){
  	$crypt = new Crypt(self::KEY);
  	$this->assertTrue($crypt instanceof Crypt, 'Testing class constructor');
    unset($crypt);
  }

  /**
   * Tests Crypt->setKey()
   */
  public function testSetKey(){
  	// Set new key
    $newkey = 'gjhfdbhTGFJUGFDbvfdJHGFNcxfdsgDHVCNBfdsgjhgfNfdhgfdhd';
    $this->crypt->setKey($newkey);
    
    // Reset to old key
    $this->crypt->setKey(self::KEY);
    
    unset($newkey);
  }

  /**
   * Tests Crypt->setAlgorithm()
   */
  public function testSetAlgorithm(){
  	// Set algorithm
    $this->crypt->setAlgorithm(MCRYPT_RIJNDAEL_256);
    
    // Reset to old algo
    $this->crypt->setAlgorithm(MCRYPT_BLOWFISH);
  }

  /**
   * Tests Crypt->encrypt()
   */
  public function testEncrypt(){
    // Encrypt Data
  	$encrypted = $this->crypt->encrypt(self::DATA);
    $this->assertTrue($encrypted != self::DATA, 'Testing data encryption');
    
    unset($encrypted);
  }

  /**
   * Tests Crypt->decrypt()
   */
  public function testDecrypt(){
  	// Encrypt the data
    $encrypted = $this->crypt->encrypt(self::DATA);
    // Decrypt the data
    $decrypted = $this->crypt->decrypt($encrypted);
    $this->assertTrue($decrypted == self::DATA, 'Testing data decryption');
    
    unset($encrypted, $decrypted);
  }

}

