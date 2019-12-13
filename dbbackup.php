<?php

chdir('..');

// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA TOOLKIT DBBACKUP must runs from console");
}
else{
    echo("----------------------------------------------").PHP_EOL;
    echo("|         ALGOMA TOOLKIT DBBACKUP            |").PHP_EOL;
    echo("----------------------------------------------").PHP_EOL;
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);


include_once 'config.inc.php';

$date = date('Ymd_His');

$cmd="mysqldump --user={$dbconfig['db_username']} --password='{$dbconfig['db_password'] }' --host={$dbconfig['db_server']} {$dbconfig['db_name']} --result-file=toolkit/{$dbconfig['db_name']}.$date.sql";
echo "Running...". PHP_EOL . $cmd. PHP_EOL;

exec($cmd);

echo "DONE" . PHP_EOL;

