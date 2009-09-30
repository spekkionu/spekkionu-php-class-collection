<?php
// Include the library
require_once('../Crypt.php');

define('CRYPT_KEY', 'gfhtHFHYthdsfREYRTGjnfdbfdsGJgfnfdgTUGFHjnfdgRFEDHYgfjgfhRTGFjfgnbhgjHHfdgfdsgFDUHYgfn');

// blowfish
// rijndael-256

// Init class with key and algorithm
$crypt = new Crypt(CRYPT_KEY, MCRYPT_RIJNDAEL_256);

// the data we are going to encrypt
$data = array(
  'name' => 'Bob',
  'gender' => 'Male',
  'chesse' => true
);
// encrypt the data
$encrypted = $crypt->encrypt($data);
// decrypt the data
$decrypted = $crypt->decrypt($encrypted);

?>
<!DOCTYPE html>
<html>
<head>
<title>Crypt Example</title>
<style>
  code{
    border: solid 1px #CCC;
    padding: 5px;
  }
</style>
</head>
<body>
  <h1>Crypt Class Example</h1>
  
  <h2>Original Data</h2>
  <code><?php var_dump($data)?></code>
  <h2>Encrypted</h2>
  <code><?php echo htmlentities($encrypted)?></code>
  <h2>Decrypted</h2>
  <code><?php var_dump($decrypted)?></code>
</body>
</html>