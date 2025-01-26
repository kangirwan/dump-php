<?php
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
include_once(dirname(__FILE__) . '/vendor/mysqldump-php/src/Ifsnop/main.inc.php');

use Ifsnop\Mysqldump as IMysqldump;
use PhpDevCommunity\DotEnv;
$env_file = __DIR__ . '/.env';
(new DotEnv($env_file))->load();

$dumpSettings = array(
    'compress' => IMysqldump\Mysqldump::GZIPSTREAM,
    'no-data' => false,
    'add-drop-table' => true,
    'single-transaction' => true,
    'lock-tables' => true,
    'add-locks' => true,
    'extended-insert' => true,
    'disable-foreign-keys-check' => true,
    'skip-triggers' => false,
    'add-drop-trigger' => true,
    'databases' => true,
    'add-drop-database' => true,
    'hex-blob' => true
);

try {

    $date = date('Ymdhms');
    $dump = new IMysqldump\Mysqldump(getenv('DATABASE_DNS'),getenv('DATABASE_USER'),getenv('DATABASE_PASSWORD'),$dumpSettings);
    $dump->start("backup/db-{$date}.sql.gz");

    echo 'berhasil backup';

} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}