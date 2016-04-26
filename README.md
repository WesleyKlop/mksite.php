# mksite.php

This is a PHP script that creates a nginx vhost from a template and automagically generates a Let's Encrypt certificate for it.  
With my current setup I score an A+ on [SSL Labs](https://ssllabs.com).

This script does not yet work for subdomains as it currently always includes a www. server name as well.

## Dependencies

* The [Let's Encrypt](https://letsencrypt.org/) client
* PHP-CLI
* Composer
* Nginx
* root permissions on your webserver

## Installation

```shell
git clone https://github.com/WesleyKlop/mksite.php
cd mksite.php
# Install dependencies/autoloader
composer install
# Maybe copy the snippets to your nginx directory
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

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).