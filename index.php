<?php
require_once __DIR__  . '/config.php';
require __DIR__ . '/vendor/autoload.php';

// Create a folder for a dumped version
if (!file_exists('path/to/directory')) {
	mkdir('dump', 0755, true);
}

use Ifsnop\Mysqldump as sql;

try {
		$dump = new sql\Mysqldump('mysql:host='.$PRD_HOST.';dbname='.$PRD_DBNAME.'', ''.$PRD_USERNAME.'', ''.$PRD_PASSWORD.'');
		$dump->start('dump/dump.sql');
} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}

$link = mysqli_connect($PRD_HOST, $PRD_USERNAME, $PRD_PASSWORD, $PRD_DBNAME);


if (!$link) {
	echo "Error: Unable to connect to MySQL." . PHP_EOL;
	echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	exit;
}

function clearDB() {
	global $link;
	global $DB_NAME;
	mysqli_query($link, "DROP DATABASE " . $DB_NAME .";");
	mysqli_query($link, "CREATE DATABASE " . $DB_NAME .";");
}

function popuateDb() {
	global $link;
	$query = file_get_contents("latest-db.sql");
	$cumulative_rows = 0;
	$db = $link;
	if(mysqli_multi_query($link, $query)){
		do {
			$cumulative_rows += mysqli_affected_rows($db);
		} while(mysqli_more_results($link) && mysqli_next_result($link));
	}
	if($error_mess = mysqli_error($link)){echo "Error: $error_mess";}
	echo "Affected Rows: $cumulative_rows";
	echo "<br>done";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<style>
		* {
			font-family: sans-serif;
			font-size: 1.1em;
		}
		i {
			font-size: 0.8em;
		}
	</style>
</head>
<body>
<?php

if (isset($_GET['confirm'])) {

	popuateDb();

} else if (!isset($_GET['update'])) {
	$dbData = file_get_contents('dump/dump.sql', 'r+');

	$file = fopen('latest-db.sql', 'w+');

	//$dbData = str_replace($STAGE_URL, $DEV_URL, $dbData);
	$dbData = str_replace("utf8mb4", "utf8", $dbData);
	$dbData = str_replace("utf8mb4_unicode_520_ci", "utf8_general_ci", $dbData);
	$dbData = str_replace("utf8_unicode_520_ci", "utf8_general_ci", $dbData);

	if (!$file) die('error');

	fwrite($file, $dbData);
	fclose($file);

	echo "db data saved from server " . date('l jS \of F Y h:i:s A');
	?>

	<hr>
	<form action="" method="GET">
		<input type="hidden" value="1" name="update">
		<button>Overrwrite my local DB?</button>
	</form>

	<?php

} else {

	// DROP DATABASE atomstore;

	clearDB();

	echo "database cleared<br>";
	echo "<a href='?confirm=1'>import sql...</a>";

}
mysqli_close($link);
?>