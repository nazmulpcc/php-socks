# php-socks
This is a Socks5 proxy server implementation built with PHP & Swoole.
To start the proxy server, clone this repo, run `composer install` to install dependencies. You must also need [Swoole](https://www.swoole.co.uk/docs/get-started/installation) php extension enabled. Then just run `php proxy.php` to start the proxy server.
Then make a request via the proxy server:

    curl -x socks5h://localhost:1080 https://www.google.com
    
  
