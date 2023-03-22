<?php

use React\EventLoop\Loop;
use Symfony\Component\Finder\Finder;


class Montior
{
    public $files = [];

    public $fileToFd = [];

    public $callback;

    public $debug = false;


    public function info($msg)
    {
        if ($this->debug) {
            echo $msg.PHP_EOL;
        }
    }

    public function removeFile($file)
    {
        unset($this->files[$file]);
    }
    public function addFile($file)
    {
        if (!isset($this->files[$file])) {
            $this->files[$file]['size'] = filesize($file);
        }

    }
    public function addFileFd($file, $fd, $watch_descriptor)
    {
        $this->fileToFd[$file] = [
            'fd' => $fd,
            'watch_descriptor' => $watch_descriptor,
        ];
    }


    public function getFileFd($file)
    {
        return $this->fileToFd[$file]['fd'];
    }
    public function getFileFdWatchDescriptor($file)
    {
        return $this->fileToFd[$file]['watch_descriptor'];
    }

    public function existFileFd($file)
    {
        return isset($this->fileToFd[$file]);
    }

    function removeTail($file) {
        if ($this->existFileFd($file)) {
            $fd = $this->getFileFd($file);
            $watch_descriptor = $this->getFileFdWatchDescriptor($file);
            unset($this->fileToFd[$file]);
            $this->removeFile($file);
            Loop::removeReadStream($fd);
            inotify_rm_watch($fd, $watch_descriptor);
            fclose($fd);

        }
       
    }

    public function run($path, $names = [], $callback = null){

        $this->callback = $callback ?: function(){};

        $finder = new Finder();

        $finder->files()->in($path);

        foreach ($names as $name) {
            $finder->name($name);
        }

        foreach ($finder as $file) {
            $this->addFile($file->getRealPath());
        }

        foreach ($this->files as $path => $value) {
            $this->tailFile($path, $value['size']);
        }
    }



    public function tailFile($file, $lastpos = 0){

        if ($this->existFileFd($file)) {
            return;
        }

        $isRead = false;
    
        list($fd, $watch_descriptor) = $this->watchFile($file);
        $this->addFileFd($file, $fd, $watch_descriptor);
        Loop::addReadStream($fd, function ($fd) use ($file, &$lastpos, &$isRead) {
            $buffer = $this->handleWatchFile($fd, $file, $lastpos, $isRead);
            if ($buffer === false) {
                $this->info("file error");
            } elseif ($buffer === null)  {
                $this->info("file is reading");
            } else  {
                $this->info("read success");
                echo "\n";
            }
        });
    }


    public function watchFile($file) {
        $fd = inotify_init();
        $watch_descriptor = inotify_add_watch($fd, $file, IN_ALL_EVENTS);
        stream_set_blocking($fd, 0);
        return [$fd, $watch_descriptor];
    }


    public function handleWatchFile($fd, $file, &$pos, &$isRead){
            
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

                    if(!$pos) $pos = filesize($file);
                    
                    // open the file
                    $fp = fopen($file,'r');
                    if (!$fp) {
                        $this->removeTail($file);
                        fclose($fp);
                        return false;
                    };
                    $isRead = true;
    
                    // seek to the last EOF position
                    fseek($fp,$pos);
    
                    // read until EOF
                    while (!feof($fp)) {
                        $callback = $this->callback;
                        $callback(fread($fp, 8192));
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
                    $this->removeTail($file);
                    // Return a failure
                    return false;
                    break;
            }
        }
    }

    public function __destruct()
    {
        foreach ($this->fileToFd as $file => $fd) {
            $this->removeTail($file);
        }
    }
    

}