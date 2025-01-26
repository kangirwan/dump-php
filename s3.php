<?php
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
include_once(dirname(__FILE__) . '/vendor/mysqldump-php/src/Ifsnop/main.inc.php');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
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

$s3 = new S3Client([
    'credentials' => [
        'key' => getenv('S3_KEY'),
        'secret' => getenv('S3_SECRET')
    ],
    'version' => 'latest',
    'region'  => getenv('S3_REGION'),
    // 'signature'	=> 'v2',
    'use_path_style_endpoint' => true,
    'endpoint' 	=> getenv('S3_ENDPOINT')
]);

try {
  
    $date = date('Ymdhms');
    $filename = "backup/db-{$date}.sql.gz";

    try {

        $dump = new IMysqldump\Mysqldump(getenv('DATABASE_DNS'),getenv('DATABASE_USER'),getenv('DATABASE_PASSWORD'),$dumpSettings);
        $dump->start($filename);

        $result = $s3->putObject([
            'Bucket' => getenv('S3_BUCKET'),
            'Key' => $filename,
            'Body' => fopen($filename, 'r'),
            // 'ACL'    => 'public-read',
        ]);

        unlink($filename);

        echo "berhasil upload: ". $result->get('ObjectURL');

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }

} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}