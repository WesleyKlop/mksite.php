#!/usr/bin/php
<?php
# Check if script is run as root...
if (getenv('USERNAME') !== 'root') {
    echo 'Please run this script as root :)' . PHP_EOL;
    exit();
}

# Constants
define('NGINX_DIR', '/etc/nginx');
define('TEMPLATE_FILE', __DIR__ . '/template.website');
define('PUBLIC_IP', '84.104.49.159');
define('LETS_ENCRYPT_DIR', '/opt/letsencrypt');
define('WEBROOT', '/var/www');

/**
 * Asks answer to question as long as the user response is empty.
 * @param string $question the question to ask.
 * @return string the answer to the question
 */
function getUserInput($question) {
    do {
        echo trim($question) . ' ';
    } while(empty(trim($input = fgets(STDIN))));
    return trim($input);
}

/**
 * Checks if a domain resolves to an IP
 * @param string $domain a domain name eg. 'wesleyklop.nl'
 * @return string|bool the ip if the domain resolves or false
 */
function doesDomainResolve($domain) {
    // Add dot so it can't accidently resolve to local IP
    $host =  $domain . '.';

    return (($ip = gethostbyname($host)) !== $host) ? $ip : false;
}

/**
 * Checks if a domain name is valid
 * @param string $domainName
 * @return bool true or false
 */
function isValidDomainName($domainName)
{
    return (preg_match(/** @lang RegExp */
            "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName) //valid chars check
        && preg_match(/** @lang RegExp */
            "/^.{1,253}$/", $domainName) //overall length check
        && preg_match(/** @lang RegExp */
            "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName)); //length of each label
}

/*---------------------------------------------------------------------------*/

# Read the template file
if(file_exists(TEMPLATE_FILE)) {
    $template = file_get_contents(TEMPLATE_FILE);
} else {
    echo 'Unable to read template file "' . TEMPLATE_FILE . '"';
    exit(1);
}

# Ask the user for the server name
$serverName = '';
do {
    $serverName = getUserInput("Enter server name:");
    if(isValidDomainName($serverName)) {
        break;
    } else {
        echo 'Invalid URL' . PHP_EOL;
    }
} while (true);

# To use Let's encrypt the domain has to exist and resolve.
# So check what the domain name resolves to and if it does resolve
# check if it equals the server's public IP
$serverIp = doesDomainResolve($serverName);
if($serverIp !== PUBLIC_IP) {
    // Server IP either does not resolve or does not equal
    // the servers public IP
    echo 'Domain did not resolve to the public ip but to ';
    echo ($serverIp ?: 'false') . PHP_EOL;
    exit(1);
}

# Everything is now ready so start writing things
# Create directory structure
$webDir = WEBROOT . '/' . $serverName;
mkdir($webDir . '/public_html', 0775, true);
mkdir('/var/log/nginx/'.$serverName, 0755);

# Create placeholder index.html file
file_put_contents($webDir . '/public_html/index.html', "<h1>Virtual host {$serverName} is working!</h1>");

# chmod, chown etc via system calls
echo system("/bin/chmod -R 775 " . escapeshellarg($webDir));
echo system("/bin/chown -R www-data:www-data " . escapeshellarg($webDir));

# Webroot should now exist so edit and write the vhost config
$vhostconf = str_replace('{{server_name}}', $serverName, $template);
$vhostconf = str_replace('{{webroot}}', WEBROOT, $vhostconf);
file_put_contents(NGINX_DIR . '/sites-available/' . $serverName, $vhostconf);

# config should exist, test it using `nginx -t`
echo system('/usr/sbin/nginx -t');
# YOLO enable the nginx vhost even if it's not valid
echo system('/bin/ln -s '. escapeshellarg(NGINX_DIR.'/sites-available/'.$serverName) .  ' /etc/nginx/sites-enabled/');

# Vhost should now be enabled so try to create the letsencrypt certs
$command = LETS_ENCRYPT_DIR . '/letsencrypt-auto certonly -a webroot --webroot-path=' . $webDir . '/public_html' .
' -d ' . escapeshellarg($serverName) . ' -d ' . escapeshellarg('www.' . $serverName);
echo shell_exec($command);

# Creating letsencrypt certs should now be done, so uncomment the ssl directives and reload nginx
$vhostconf = str_replace("#inc", "inc", $vhostconf);
$vhostconf = str_replace("#ssl", "ssl", $vhostconf);
$vhostconf = str_replace("#ret", "ret", $vhostconf);
file_put_contents('/etc/nginx/sites-available/' . $serverName, $vhostconf);

echo system('/bin/systemctl reload nginx') . PHP_EOL;

echo 'Your site should now be accessible via HTTPS! :D' . PHP_EOL .
'Website root => ' . WEBROOT . PHP_EOL .
'Site config file => ' . NGINX_DIR . '/sites-available/' . $serverName . PHP_EOL .
'Certificate location => ' . LETS_ENCRYPT_DIR . '/live/' . $serverName . PHP_EOL;
