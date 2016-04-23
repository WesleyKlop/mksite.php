#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 10:55 PM
 */
require_once '../vendor/autoload.php';

use Mksite\Validator;


echo "Current user: " . getenv('USERNAME') . PHP_EOL;

echo "Current user is root: " . (Validator::isScriptExecutedAsSuperUser(getenv('USERNAME')) ? 'yes' : 'no') . PHP_EOL;

echo "shell_exec whoami output: " . shell_exec('/bin/whoami') . PHP_EOL;