<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 10:40 PM
 */

namespace Mksite;


class ArgumentHolder
{
    /**
     * @var array $args array containing the default options
     */
    protected $args = array(
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

    protected $shortOpts = 'w:n:t:i:l:L:s:S:';
    /**
     * @var array $longOpts string containing long options
     */
    protected $longOpts = array(
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
    public function parseOpts()
    {
        $options = getopt($this->shortOpts, $this->longOpts);

        foreach ($options as $key => $option) {
            echo "{$key} => {$option}\n";
            switch ($key) {
                case 'w':
                case 'webroot':
                    $this->setArg('webroot-dir', $option);
                    break;
                case 'n':
                case 'nginx':
                    $this->setArg('nginx-dir', $option);
                    break;
                case 't':
                case 'template':
                    $this->setArg('template-file', $option);
                    break;
                case 'i':
                case 'ip':
                    $this->setArg('public-ip', $option);
                    break;
                case 'l':
                case 'lets-encrypt':
                    $this->setArg('lets-encrypt-dir', $option);
                    break;
                case 'L':
                case 'log':
                    $this->setArg('log-dir', $option);
                    break;
                case 's':
                case 'subdomain':
                    $this->setArg('subdomain', $option);
                    break;
                case 'S':
                case 'superuser':
                    $this->setArg('superuser', $option);
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
    public function setArg($argument, $value)
    {
        if (!array_key_exists($argument, $this->args))
            throw new \InvalidArgumentException('Argument "' . $argument . '" not found!');
        if (!isset($value))
            throw new \Exception('Empty value!');

        $this->args[$argument] = $value;
    }

    /**
     * @return array the array containing the arguments
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * @param string $argument the argument to get
     * @return string the arguments value
     * @throws \InvalidArgumentException when the argument is not found
     */
    public function getArg($argument) : string
    {
        if (array_key_exists($argument, $this->args)) {
            return $this->args[$argument];
        }
        throw new \InvalidArgumentException('Argument "' . $argument . '" not found!');
    }
}