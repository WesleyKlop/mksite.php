#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: wesley
 * Date: 4/23/16
 * Time: 11:12 PM
 */
require_once '../vendor/autoload.php';

\Mksite\Arguments::parseArgv($argv);

var_dump(\Mksite\Arguments::getArgs());