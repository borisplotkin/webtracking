<?php

function localdb_connect() {
	$host = '';
	$username = '';
	$password = '';
	$dbname = ''; 
	
	// $host = 'localhost';
	// $username = 'root';
	// $password = '';
	// $dbname = 'analytics'; 
	//Connect to database server
	$connection = mysql_connect($host,$username,$password);
	//testing to see the connection works
	if (!$connection){
	   die('Error: '.mysql_error());	
	}
	//Selecting the database name and testing if it exists
	if (!mysql_select_db($dbname)){
	   die('Error: '. mysql_error());	
	}
	return $connection;
}
function remotedb_connect() {
	$host = '';
	$username = '';
	$dbname = ''; 
	$password = '';
	
	// $host = 'localhost';
	// $username = 'root';
	// $password = '';
	// $dbname = 'dev'; 
	//Connect to database server
	$connection = mysql_connect($host,$username,$password);
	//testing to see the connection works
	if (!$connection){
	   die('Error: '.mysql_error());	
	}
	//Selecting the database name and testing if it exists
	if (!mysql_select_db($dbname)){
	   die('Error: '. mysql_error());	
	}
	return $connection;
}
function remotedb2_connect() {
	$host = '';
	$username = 'admin';
	$dbname = ''; 
	$password = '';
	
	// $host = 'localhost';
	// $username = 'root';
	// $password = '';
	// $dbname = 'dev'; 
	//Connect to database server
	$connection = mysql_connect($host,$username,$password);
	//testing to see the connection works
	if (!$connection){
	   die('Error: '.mysql_error());	
	}
	//Selecting the database name and testing if it exists
	if (!mysql_select_db($dbname)){
	   die('Error: '. mysql_error());	
	}
	return $connection;
}
?>