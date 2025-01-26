<?php
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
include_once(dirname(__FILE__) . '/vendor/mysqldump-php/src/Ifsnop/main.inc.php');

use Ifsnop\Mysqldump as IMysqldump;
use PhpDevCommunity\DotEnv;
$env_file = __DIR__ . '/.env';
(new DotEnv($env_file))->load();

$user = getenv('DATABASE_USER');
$pass = getenv('DATABASE_PASSWORD');
$dbname = getenv('DATABASE_NAME');
$dbhost = getenv('DATABASE_HOST');

$cdb = new PDO("mysql:host={$dbhost};dbname={$dbname}", $user, $pass);

try {

    foreach($cdb->query("SHOW TABLES IN `{$dbname}`") as $row) {
        $table = current($row);
        $dump = new IMysqldump\Mysqldump("mysql:host={$dbhost};dbname={$dbname}", $user, $pass, array("include-tables" => array($table)));
        $dump->start("backup/{$table}.sql");
    }

    echo 'berhasil backup';

} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}