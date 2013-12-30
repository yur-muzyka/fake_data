<?php

require 'fake.php';

//========== b-db ==============
$db = mysql_connect('localhost', 'root', '123');
mysql_select_db('shop', $db);
mysql_query("set names 'utf8'");
//========== e-db ==============
set_time_limit(0);




$fake = new Fake();
var_dump($fake->title(40));
die();



for ($n = 0; $n < 4; $n++) {
    $fake = new Fake();
    $query = mysql_query('insert into Product values(0, "' . $fake->solid(20) . '", "' . $fake->text(2000) . '", 43.34)');
}
                        
?>
