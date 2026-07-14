<?php
function db_connect() {
	$servername = "sql106.infinityfree.com";
	$username = "if0_42397405";
	$password = "Byyc0619";
	$database = "if0_42397405_CommunityServiceDB";

	$conn = mysqli_connect($servername, $username, $password, $database);

	if(!$conn) {
		die("Database connection failed: " . mysqli_connect_error());
	} 

	return $conn;
}
?>