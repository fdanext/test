<?php

require_once('settings.php');

function connect_db() {
	$conn = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

	if (mysqli_connect_errno()) {
		printf("Соединение не удалось: %s\n", mysqli_connect_error());
		exit();
	}

	return $conn;
}

function close_db($conn) {
	mysqli_close($conn);
}

?>