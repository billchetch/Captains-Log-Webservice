<?php
use chetch\Config as Config;

Config::initialise();

Config::set('ERROR_REPORTING', E_ALL);

//Database Config
Config::set('DBHOST', 'mysql:host=127.0.0.1');
Config::set('DBNAME', 'captains_log');
Config::set('DBUSERNAME', 'rogon');
Config::set('DBPASSWORD', 'frank1yn');
$dbtblpfx = ''; //table prefix for database

//Email Config
/*Config::set('EMAIL_EXCEPTIONS_TO', 'bill@bulan-baru.com');
Config::set('PHP_MAILER', _SITESROOT_.'webapps/lib/php/phpmailer/class.phpmailer.php');
Config::set('SMTP_HOST', _SMTP_HOST_);
Config::set('SMTP_SECURE', _SMTP_SECURE_);
Config::set('SMTP_USERNAME', _SMTP_USERNAME_);
Config::set('SMTP_PASSWORD', _SMTP_PASSWORD_);
Config::set('SMTP_PORT', _SMTP_PORT_);*/

//API Config
Config::set('API_ALLOW_REQUESTS', 'GET,PUT,POST,DELETE');


//establish table names


?>