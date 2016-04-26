<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 10:25 PM
 */

namespace Mksite;

/**
 * Class Mksite
 * @package Mksite
 */
class MkSite
{
    protected $webDir;
    /**
     * @var string $template the temporary server config
     */
    protected $template;
    /**
     * @var string $siteName the name of the Virtual Host
     */
    protected $siteName;
    /**
     * @var ArgumentHolder $args
     */
    protected $args;
    /**
     * @var Validator $validator
     */
    protected $validator;

    /**
     * MkSite constructor.
     * @param ArgumentHolder $args
     * @param Validator $validator
     */
    public function __construct(ArgumentHolder $args, Validator $validator)
    {
        $this->args = $args;
        $this->validator = $validator;

        $this->args->parseOpts();

        if (!$this->validator->isScriptExecutedAsSuperUser(getenv("USERNAME")))
            die("This script needs to be run as root!" . PHP_EOL);
    }

    /**
     * Main function
     * @throws \Exception when the siteName does not resolve to the correct IP
     */
    public function MkSite()
    {
        // Get the template file
        $template_path = realpath($this->args->getArg('template-file'));
        echo "Using template at " . $template_path . PHP_EOL;
        $this->template = $this->getTemplate($template_path);

        // Ask the user for the site name
        $this->siteName = $this->getSiteName();

        // Ask if we should generate a www. server_name
        $generateWww = $this->askYesNoQuestion('Should I generate a www. subdomain that points to the site name too?');
        $this->args->setArg('subdomain', $generateWww);

        // Verify that the domain name resolves to $this->args->getArg('public-ip');
        echo "Testing if the url resolves to your public IP..." . PHP_EOL;
        $domainIp = gethostbyname($this->siteName);
        if ($domainIp !== $this->args->getArg('public-ip'))
            throw new \Exception($this->siteName . " does not resolve to ip " . $this->args->getArg('public-ip') . "!");
        echo $this->siteName . " resolves to " . $domainIp . PHP_EOL;

        // Checks completed, start writing configs and dirs
        $this->createDirectories();
        $this->writeVHostConfig();

        // Enable the virtual host and reload
        echo system('/bin/ln -s ' . escapeshellarg($this->args->getArg('nginx-dir') . '/sites-available/' . $this->siteName) . ' /etc/nginx/sites-enabled/');
        echo $this->reloadNginx();

        // Do a quick config test
        echo system('/usr/bin/nginx -t');

        // Create the let's encrypt certificates
        $this->createLetsEncryptCerts();
        // Change the config files to make use of it
        $this->setSSLConfig();
        // Reload nginx
        echo $this->reloadNginx();

        // Done!
        echo 'Your site should now be accessible via HTTPS! :D' . PHP_EOL .
            'Website root => ' . $this->args->getArg('webroot-dir') . '/' . $this->siteName . '/public_html' . PHP_EOL .
            'Site config file => ' . $this->args->getArg('nginx-dir') . '/sites-available/' . $this->siteName . PHP_EOL .
            'Certificate location => /etc/letsencrypt/live/' . $this->siteName . PHP_EOL .
            'Website location: https://' . $this->siteName . PHP_EOL;
    }

    /**
     * Returns the template config file as a string
     * @param string $templateFile the path to the template file
     * @return string the template file
     * @throws \Exception when the file is not found
     */
    private function getTemplate(string $templateFile) : string
    {
        // Check if the file exists
        if (file_exists($templateFile)
            && is_readable($templateFile)
        ) {
            return file_get_contents($templateFile);
        }
        throw new \Exception("Template file not accessible!");
    }

    /**
     * Asks the user for a valid domain name
     * @return string a valid domain name
     */
    private function getSiteName() : string
    {
        do {
            echo "Enter the site name: ";
            $input = trim(fgets(STDIN));
        } while (!$this->validator->isValidDomainName($input));

        return $input;
    }

    public function askYesNoQuestion(string $question) :string
    {
        $matches = [];
        do {
            echo trim($question) . ' ';
            $input = preg_match(/** @lang RegExp */
                "/^y(?:es)?$|^n(?:o)?$/ix", fgets(STDIN), $matches);
        } while ($input !== 1);

        return $matches[0] === 'y';
    }

    /**
     * Create the site directories, index file and set the correct permissions
     */
    private function createDirectories()
    {
        $this->webDir = $this->args->getArg('webroot-dir') . '/' . $this->siteName;
        // Create the site root, by default will be /var/www/{siteName}/public_html
        echo "Creating directory " . $this->webDir . '/public_html' . PHP_EOL;
        mkdir($this->webDir . '/public_html', 0775, true);
        // Create log directory
        echo "Creating directory " . $this->args->getArg('log-dir') . '/' . $this->siteName . PHP_EOL;
        mkdir($this->args->getArg('log-dir') . '/' . $this->siteName);

        // Create - temporary - index.html file in the site root
        file_put_contents($this->webDir . '/public_html/index.html', "<h1>Virtual host {$this->siteName} is working!</h1>");

        // Set the correct permissions
        // TODO: Add argument option for file owner and permissions
        echo system("/bin/chmod -R 775 " . escapeshellarg($this->webDir));
        echo system("/bin/chown -R www-data:www-data " . escapeshellarg($this->webDir));
    }

    /**
     * Write the virtual host template to the Nginx dir
     * @todo: template argument that replaces the str_replace calls
     */
    private function writeVHostConfig()
    {
        // Set template keys
        $this->template = str_replace('{{server_name}}', $this->siteName, $this->template);
        $this->template = str_replace('{{webroot}}', $this->args->getArg('webroot-dir'), $this->template);

        echo "Creating subdomain config: " . ($this->args->getArg('subdomain') ? 'yes' : 'no') . PHP_EOL;
        if ($this->args->getArg('subdomain')) {
            $this->template = str_replace('{{www_server_name}}', '', $this->template);
        } else {
            $this->template = str_replace('{{www_server_name}}', 'www.' . $this->siteName, $this->template);
        }

        // Insert into the config
        file_put_contents($this->args->getArg('nginx-dir') . '/sites-available/' . $this->siteName, $this->template);
    }

    /**
     * Reload Nginx using systemctl(Systemd)
     * @return mixed
     */
    protected function reloadNginx()
    {
        return system('/bin/systemctl reload nginx');
    }

    /**
     * Creates the ssl certificates and afterwards changes the config accordingly
     */
    private function createLetsEncryptCerts()
    {
        $command = $this->args->getArg('lets-encrypt-dir') . '/letsencrypt-auto certonly -a webroot --webroot-path=' . escapeshellarg($this->webDir . '/public_html') .
            ' -d ' . escapeshellarg($this->siteName);
        // Append the www. domain if the site is not a subdomain
        $command .= !$this->args->getArg('subdomain') ? ' -d ' . escapeshellarg('www.' . $this->siteName) : '';
        echo "executing:" . PHP_EOL . $command . PHP_EOL;
        echo system($command);
    }

    /**
     * Replace all occurrences of #{{ssl}} located in the template file with nothing
     */
    private function setSSLConfig()
    {
        $this->template = str_replace('#{{ssl}}', '', $this->template);
        file_put_contents($this->args->getArg('nginx-dir') . '/sites-available/' . $this->siteName, $this->template);
    }
}