<?php

require 'fake.php';

set_time_limit(0);
$fake = new Fake();
echo $fake->text(10000);
echo "<hr>";
echo $fake->solid(10000);
                        
?>
