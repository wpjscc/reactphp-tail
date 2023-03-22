* 依赖扩展 
    * https://www.php.net/manual/zh/function.inotify-init.php
    * inotify
    ```
    apt install php-pear
    pecl install inotify ev
    # 扩展放入php.ini
    ```


```
 php tail.php --path=/root/Code/reactphp-tail/logs  --name='*test.log' --name='*test1.log'
```


```
docker build -t wpjscc/tail . -f Dockerfile
```


```
docker push wpjscc/tail
```

运行
```
docker run -it --rm -v /var/log/k8s:/var/log/k8s -v /var/log/k3s:/var/log/k3s wpjscc/tail php tail.php --path=/var/log/k8s  --path=/var/log/k3s --name='*.log' --debug=1
```

停止
```
docker ps | grep tail

docker stop xxxx

```

