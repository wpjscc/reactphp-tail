<?php

// $a = [1,4,5];

// $key = array_search(4, $a);

// var_dump($key);

// exit();

// $file = __DIR__.'/logs/test1.log';

// $pos = filesize($file);

// var_dump($pos);

// $fp = fopen($file , 'r');

// fseek($fp,$pos);

// while (!feof($fp)) {
//     echo fread($fp, 8192)."--\n";
// }

// fclose($fp);


// exit();


for ($i=0; $i < 1000; $i++) { 
    if ($i%50) {
        sleep(1);
        echo $i."\n";
    }
    file_put_contents(__DIR__.'/logs/test1.log', $i.PHP_EOL, FILE_APPEND);
}