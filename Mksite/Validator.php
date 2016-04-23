<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 10:52 PM
 */

namespace Mksite;

/**
 * Class Validator this class validates values to see if they are allowed in the given context
 * @package Mksite
 */
class Validator
{
    public static function isScriptExecutedAsSuperUser($user)
    {
        return $user == Arguments::getArg('superuser');
    }

    /**
     * Checks if a domain resolves to an IP
     * @param string $domainName a domain name eg. 'wesleyklop.nl'
     * @return string|bool the ip if the domain resolves or false
     */
    public static function doesDomainResolve($domainName)
    {
        // Add dot so it can't accidently resolve to local IP
        $host = $domainName . '.';

        return (($ip = gethostbyname($host)) !== $host) ? $ip : false;
    }

    /**
     * Checks if a domain name is valid
     * @param string $domainName
     * @return bool true or false
     */
    public static function isValidDomainName($domainName)
    {
        return (preg_match(/** @lang RegExp */
                "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName) //valid chars check
            && preg_match(/** @lang RegExp */
                "/^.{1,253}$/", $domainName) //overall length check
            && preg_match(/** @lang RegExp */
                "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName)); //length of each label
    }

    public static function isSubDomain($siteName)
    {
        // Simply by checking if the string contains multiple dots
        return preg_match('/(.*)\.(.*)\.(.*)/', $siteName) ? true : false;
    }
}