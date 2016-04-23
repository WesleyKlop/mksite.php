# mksite.php

This is a PHP script that creates a nginx vhost from a template and automagically generates a Let's Encrypt certificate for it.  
With my current setup I score an A+ on [https://ssllabs.com](SSL Labs).

This script does not yet work for subdomains as it currently always includes a www. server name as well

## Dependencies

* The [https://letsencrypt.org/](Let's Encrypt) client
* php-cli
* nginx
* root permissions on your webserver

## Installation

```shell
git clone https://github.com/WesleyKlop/mksite.php
cd mksite.php
# Maybe copy the snippets to your nginx directory
# The snippets/php-fpm.conf file assumes you use PHP 7
# The snippets/default-https.conf also enables http/2
#cp snippets/* /etc/nginx/snippets/
# Edit the variables inside mksite.php
$EDITOR mksite.php
# Run the script AS ROOT
sudo php ./mksite.php
```

## TODO
* Subdomain support
* Better config

## License

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
