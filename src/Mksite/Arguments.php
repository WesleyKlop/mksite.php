<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 10:40 PM
 */

namespace Mksite;


class Arguments
{
    /**
     * @var array $args array containing the default options
     */
    protected static $args = array(
        "webroot-dir" => '/var/www',
        "nginx-dir" => '/etc/nginx',
        "template-file" => __DIR__ . '/../../template.website',
        "public-ip" => '123.45.67.89',
        "lets-encrypt-dir" => '/opt/letsencrypt',
        "log-dir" => '/var/log/nginx',
        "subdomain" => false,
        "superuser" => 'root'
    );

    /**
     * @var string $shortOpts string containg short options
     */

    protected static $shortOpts = 'w:n:t:i:l:L:s:S:';
    /**
     * @var array $longOpts string containing long options
     */
    protected static $longOpts = array(
        'webroot:',
        'nginx:',
        'template:',
        'ip:',
        'lets-encrypt:',
        'log:',
        'subdomain:',
        'superuser:'
    );

    /**
     * Parses the command line options and sets the arguments variable accordingly
     * @throws \Exception
     */
    public static function parseOpts()
    {
        $options = getopt(self::$shortOpts, self::$longOpts);

        foreach ($options as $key => $option) {
            echo "{$key} => {$option}\n";
            switch ($key) {
                case 'w':
                case 'webroot':
                    self::setArg('webroot-dir', $option);
                    break;
                case 'n':
                case 'nginx':
                    self::setArg('nginx-dir', $option);
                    break;
                case 't':
                case 'template':
                    self::setArg('template-file', $option);
                    break;
                case 'i':
                case 'ip':
                    self::setArg('public-ip', $option);
                    break;
                case 'l':
                case 'lets-encrypt':
                    self::setArg('lets-encrypt-dir', $option);
                    break;
                case 'L':
                case 'log':
                    self::setArg('log-dir', $option);
                    break;
                case 's':
                case 'subdomain':
                    self::setArg('subdomain', $option);
                    break;
                case 'S':
                case 'superuser':
                    self::setArg('superuser', $option);
                    break;
                default:
                    throw new \InvalidArgumentException($key . ' is not a valid argument!');
            }
        }
    }

    /**
     * @param string $argument the arguments key
     * @param mixed $value the new value of the argument
     * @throws \InvalidArgumentException when the argument is not found
     * @throws \Exception when the argument value is empty
     */
    public static function setArg($argument, $value)
    {
        if (!array_key_exists($argument, self::$args))
            throw new \InvalidArgumentException('Argument "' . $argument . '" not found!');
        if (!isset($value))
            throw new \Exception('Empty value!');

        self::$args[$argument] = $value;
    }

    /**
     * @return array the array containing the arguments
     */
    public static function getArgs() : array
    {
        return self::$args;
    }

    /**
     * @param string $argument the argument to get
     * @return string the arguments value
     * @throws \InvalidArgumentException when the argument is not found
     */
    public static function getArg($argument) : string
    {
        if (array_key_exists($argument, self::$args)) {
            return self::$args[$argument];
        }
        throw new \InvalidArgumentException('Argument "' . $argument . '" not found!');
    }

    /**
     * @param $argv
     * @throws \Exception
     * @deprecated
     */
    public static function parseArgv(&$argv)
    {
        // Start from one because the script name is not an argument
        // Increase by 2 every time because it's `--key value` format
        for ($i = 1; $i < count($argv); $i += 2) {
            $key = self::trimArg($argv[$i]);
            // Validate that the key has a value
            if (array_key_exists($i + 1, $argv))
                $value = $argv[$i + 1];
            else
                throw new \Exception('Key ' . $key . ' does not have a value!');

            // Check if the key exists in the $arguments array
            if (!array_key_exists($key, self::$args))
                throw new \Exception('Key ' . $key . ' is not a valid argument!');

            echo "{$key} => {$value} is valid!";

            // Set the argument value
            self::setArg($key, $value);
        }
    }

    /**
     * @param $arg
     * @return mixed
     * @deprecated
     */
    private static function trimArg(&$arg)
    {
        $arg = ltrim($arg, '-');
        return $arg;
    }
}