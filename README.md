* 依赖扩展 
    * https://www.php.net/manual/zh/function.inotify-init.php
    * inotify
    ```
    apt install php-pear
    pecl install inotify
    # 扩展放入php.ini
    ```


```
 php init.php --path=/root/Code/reactphp-tail/logs  --name='*test.log' --name='*test1.log'
```