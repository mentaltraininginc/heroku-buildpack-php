http {
    include       mime.types;
    default_type  application/octet-stream;

    #log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
    #                  '$status $body_bytes_sent "$http_referer" '
    #                  '"$http_user_agent" "$http_x_forwarded_for"';

    #access_log  logs/access.log  main;

    sendfile        on;
    #tcp_nopush     on;

    #keepalive_timeout  0;
    keepalive_timeout  65;

    #gzip  on;

    server_tokens off;

    fastcgi_buffers 256 4k;

    # define an easy to reference name that can be used in fastgi_pass
    upstream heroku-fcgi {
        #server 127.0.0.1:4999 max_fails=3 fail_timeout=3s;
        server unix:/tmp/heroku.fcgi.<?=getenv('PORT')?:'8080'?>.sock max_fails=3 fail_timeout=3s;
        keepalive 16;
    }
    
    server {
        server_name localhost;
        root "<?=getenv('DOCUMENT_ROOT')?:getenv('HEROKU_APP_DIR')?:getcwd()?>";

        location @heroku-fcgi {
            include fastcgi_params;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info if_not_empty;
            if (!-f $document_root$fastcgi_script_name) { return 404; }            
            fastcgi_pass heroku-fcgi;
        }
        listen <?=getenv('PORT')?:'8080'?>;
        port_in_redirect off;
        error_log stderr;
        access_log /tmp/heroku.nginx_access.<?=getenv('PORT')?:'8080'?>.log;
        include "<?=getenv('HEROKU_PHP_NGINX_CONFIG_INCLUDE')?>";
        location ~ /\. { deny all; } # restrict access to hidden files, just in case
        location ~ \.php { try_files @heroku-fcgi @heroku-fcgi; } # default handling of .php
    }

    server {
        server_name certifiedmentaltrainer.com;
        root "<?=getenv('DOCUMENT_ROOT')?:getenv('HEROKU_APP_DIR')?:getcwd()?>/certifiedmentaltrainer-com/";

        location @heroku-fcgi {
            include fastcgi_params;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info if_not_empty;
            if (!-f $document_root$fastcgi_script_name) { return 404; }            
            fastcgi_pass heroku-fcgi;
        }
        listen <?=getenv('PORT')?:'8080'?>;
        port_in_redirect off;
        error_log stderr;
        access_log /tmp/heroku.nginx_access.<?=getenv('PORT')?:'8080'?>.log;
        include "<?=getenv('HEROKU_PHP_NGINX_CONFIG_INCLUDE')?>";
        location ~ /\. { deny all; } # restrict access to hidden files, just in case
        location ~ \.php { try_files @heroku-fcgi @heroku-fcgi; } # default handling of .php
    }
}