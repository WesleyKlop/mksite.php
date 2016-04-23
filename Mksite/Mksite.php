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
class Mksite
{
    /**
     * @var string $template the temporary server config
     */
    protected $template;
    /**
     * @var string $siteName the name of the Virtual Host
     */
    protected $siteName;

    public static function Mksite()
    {
        $self = new self();
        Arguments::parseArgv($argv);

        if (!Validator::isScriptExecutedAsSuperUser(getenv("USERNAME")))
            die("This script needs to be run as root!" . PHP_EOL);

        // Get the template file
        $template_path = realpath(Arguments::getArg('template-file'));
        $self->template = self::getTemplate($template_path);

        // Ask the user for the site name
        $self->siteName = self::getSiteName();
        // Check if the site name is a subdomain
        $isSubDomain = Validator::isSubDomain($self->siteName);
        Arguments::setArg('subdomain', $isSubDomain);
        echo "site is subdomain: " . (Arguments::getArg('subdomain') ? 'yes' : 'no') . PHP_EOL;
    }

    private static function getTemplate($templateFile) : string
    {
        // Check if the file exists
        if (file_exists($templateFile)
            && is_readable($templateFile)
        ) {
            return file_get_contents($templateFile);
        }
        throw new \Exception("Template file not accessible!");
    }

    private static function getSiteName()
    {
        do {
            echo "Enter the site name: ";
            $input = trim(fgets(STDIN));
        } while (!Validator::isValidDomainName($input));

        return $input;
    }
}