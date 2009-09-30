<?php
require_once('../../lib/StatesOptions.php');

$states_options = StatesOptions::getOptions();

$array = StatesOptions::getArray();

$pairs = StatesOptions::getPairs();

?>
<!DOCTYPE html>
<html>
<head>
<title>States Parsing Class</title>
<style type="text/css">
  code{
    display:block;
    padding:8px;
    margin: 5px 0;
    white-space:pre;
    border: solid 1px #CCC;
    background-color:#EEE;
  }
</style>
</head>
<body>
  <h1>States Parsing Class</h1>
  <div>
    <h2>Options String</h2>
    <code>&lt;?php $states_options = StatesOptions::getOptions(); ?&gt;
&lt;select name=&quot;states&quot;&gt;
  &lt;?php echo $states_options; ?&gt;
&lt;/select&gt;</code>
    <label for="states">State:</label>
    <select id="states">
      <?php echo $states_options; ?>
    </select>
  </div>
  
  <div>
    <h2>Options Array</h2>
    <code>&lt;?php $array = StatesOptions::getArray(); ?&gt;</code>
    <?php echo var_dump($array); ?>
  </div>
  
  <div>
    <h2>Options Pairs Array</h2>
    <code>&lt;?php $pairs = StatesOptions::getPairs(); ?&gt;</code>
    <?php echo var_dump($pairs); ?>
  </div>
  
</body>
</html>