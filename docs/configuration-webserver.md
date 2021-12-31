Web Server Configuration
========================

NGINX
-----

```
location ~* /render/(.*)$ {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~* ^.+\.(?:avif|gif|jpe?g|jpeg|jpg|png|svg|svgz|webp)$ {
    access_log          off;
    log_not_found       off;
    expires             max;
    add_header          Access-Control-Allow-Origin "*";
    add_header          Cache-Control "public, mustrevalidate, proxy-revalidate";
    add_header          Pragma public;
}
```

Apache
------

**TODO**

