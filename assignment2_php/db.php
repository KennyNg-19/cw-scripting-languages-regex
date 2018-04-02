<?php
	$db_hostname = "mysql";
	$db_database = "x7yw2";
	$db_username = "x7yw2";
	$db_password = "wyh425322";
	$db_charset = "utf8mb4";
	
	$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
	
	$opt = array(
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false
	);
	
