<?php
/**Loading the root autoload file, so we instantiate dotenv */
require_once  __DIR__  . '/../../../../vendor/autoload.php';
/** @desc this instantiates Dotenv and passes in our path to .env */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__  .'/../../../../config');
$dotenv->load();

require_once __DIR__.'/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Tests\\", __DIR__, true);
$classLoader->register(); 

define('TESTING_DB',true);
$test_db_name = $_ENV['TEST_DB_NAME'];
$db_name = $_ENV['DB_NAME'];

$pdo = new PDO( 'mysql:host=localhost', $_ENV['DB_USER'], $_ENV['DB_PASSWORD'] );
//
//$pdo->exec("USE {$test_db_name}");
$pdo->exec("DROP DATABASE IF EXISTS {$test_db_name}");
$pdo->exec("CREATE DATABASE {$test_db_name}");
$pdo->exec('SET SQL_MODE="ALLOW_INVALID_DATES"');
$tables = $pdo->query("SHOW TABLES FROM {$db_name}")->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table){
    $pdo->exec( "CREATE TABLE {$test_db_name}.{$table} LIKE {$db_name}.{$table}" );
    $pdo->exec("INSERT INTO `{$test_db_name}`.`{$table}` SELECT * FROM `{$db_name}`.`{$table}`");
}
//Old mysqli implementation. Keeping for now
/* $mysqli = new mysqli( 'localhost', $_ENV['DB_USER'], $_ENV['DB_PASSWORD'] ) or die( $mysqli->error );
$mysqli->query('DROP DATABASE IF EXISTS '.$test_db_name);
$mysqli->query('CREATE DATABASE '.$test_db_name);
$mysqli->query('SET SQL_MODE="ALLOW_INVALID_DATES"');
$tables = $mysqli->query( "SHOW TABLES FROM $db_name" ) or die( $mysqli->error );

while( $table = $tables->fetch_array() ): $TABLE = $table[0];
    $mysqli->query( "CREATE TABLE $test_db_name.$TABLE LIKE $db_name.$TABLE" ) or die( $mysqli->error );
    $rows = $mysqli->query( "SELECT COUNT(*) FROM $db_name.$TABLE" ) or die( $mysqli->error );
    if( $rows->fetch_array()['COUNT(*)'] > 0 ):
        $mysqli->query( "INSERT INTO $test_db_name.$TABLE SELECT * FROM $db_name.$TABLE" ) or die( $mysqli->error );
    endif;
endwhile;
*/
register_shutdown_function(function() use ($pdo) {
    $test_db_name = $_ENV['TEST_DB_NAME'];
    $pdo->exec("DROP DATABASE IF EXISTS {$test_db_name}");
    $pdo=null;
});

require_once __DIR__."/../../../../wp-load.php";