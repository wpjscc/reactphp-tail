<?php



for ($i=0; $i < 1000; $i++) { 
    if ($i%50) {
        sleep(1);
        echo $i."\n";
    }
    file_put_contents(__DIR__.'/logs/test.log', $i.PHP_EOL, FILE_APPEND);
}