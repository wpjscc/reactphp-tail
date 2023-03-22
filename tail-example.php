<?php

require __DIR__ . '/vendor/autoload.php';


use React\EventLoop\Loop;


$file = __DIR__.'/logs/test.log';



tailFile($file);


function callback($buffer){
    echo $buffer;
}

function tailFile($file){
    $lastpos = 0;
    $isRead = false;

    list($fd, $watch_descriptor) = watchFile($file);
    
    Loop::addReadStream($fd, function ($fd) use ($file, $watch_descriptor, &$lastpos, &$isRead) {
        $buffer = handleWatchFile($fd, $file, $watch_descriptor, $lastpos, $isRead);
        if ($buffer === false) {
            echo "file error\n";
        } elseif ($buffer === null)  {
            echo "file is reading\n";
        } else  {
            echo "is read\n";
        }
    });
}





function watchFile($file) {
    $fd = inotify_init();
    $watch_descriptor = inotify_add_watch($fd, $file, IN_ALL_EVENTS);
    stream_set_blocking($fd, 0);
    return [$fd, $watch_descriptor];
}

function removeTail($fd, $watch_descriptor) {
    Loop::removeReadStream($fd);
    inotify_rm_watch($fd, $watch_descriptor);
    fclose($fd);
}

function handleWatchFile($fd, $file, &$watch_descriptor, &$pos, &$isRead){
    
    $events = inotify_read($fd);

    // exit();
    foreach ($events as $event=>$evdetails) {
        // React on the event type
        switch (true) {
            // File was modified
            case ($evdetails['mask'] & IN_MODIFY):
                // Stop watching $file for changes
                // inotify_rm_watch($fd, $watch_descriptor);
                // Close the inotify instance
                // fclose($fd);
                // Loop::removeWriteStream($fd);
                if ($isRead) {
                    return ;
                }
                // open the file
                $fp = fopen($file,'r');
                if (!$fp) {
                    removeTail($fd, $watch_descriptor);
                    return false;
                };
                $isRead = true;

                // seek to the last EOF position
                fseek($fp,$pos);

                // read until EOF
                while (!feof($fp)) {
                    $b = fgets($fp);
                    callback($b);
                }
                // save the new EOF to $pos
                $pos = ftell($fp); // (remember: $pos is called by reference)
                // close the file pointer
                fclose($fp);

                $isRead = false;

                // return the new data and leave the function
                return true;
                // be a nice guy and program good code ;-)
                break;

                // File was moved or deleted
            case ($evdetails['mask'] & IN_MOVE):
            case ($evdetails['mask'] & IN_MOVE_SELF):
            case ($evdetails['mask'] & IN_DELETE):
            case ($evdetails['mask'] & IN_DELETE_SELF):
                Loop::removeReadStream($fd);
                // Stop watching $file for changes
                inotify_rm_watch($fd, $watch_descriptor);
                // Close the inotify instance
                fclose($fd);
                // Return a failure
                return false;
                break;
        }
    }
}


Loop::addPeriodicTimer(5, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});