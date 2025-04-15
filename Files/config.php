<?php
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'Jonathan');
define('DB_PASSWORD', 'Umali');
define('DB_NAME', 'fantastic');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false)
{
	die("ERROR: Could not connect," . mysqli_connect_error());
}

date_default_timezone_set('Asia/Manila');

?>