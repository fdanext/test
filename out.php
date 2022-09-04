<?php

require('settings.php');


if (isset($_GET['query']) && strlen($_GET['query']) >= MIN_LENGTH) {

	require('db.php');
	
	
	// connect DB
	$conn = connect_db();
	
	// prepare SQL for comments
	$query = "%" . $_GET['query'] . "%";
	$sql_comments = "
		SELECT blog_id, name, email, body 
		FROM comments
		WHERE body LIKE ?";
	
	$stmt = mysqli_prepare($conn, $sql_comments);

	mysqli_stmt_bind_param($stmt, "s", $query);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $blog_id, $comment_name, $comment_email, $comment_body);
	mysqli_stmt_store_result($stmt);
	
	// prepare SQL for blogs
	$sql_blogs = "
		SELECT title 
		FROM blogs
		WHERE id=?";
	$result_arr = array();

	// get data
	while (mysqli_stmt_fetch($stmt)) {
		if (array_search($blog_id, array_column($result_arr, 'blog_id')) === false) {
			
			$stmt_blogs = mysqli_prepare($conn, $sql_blogs);

			mysqli_stmt_bind_param($stmt_blogs, "s", $blog_id);
			mysqli_stmt_execute($stmt_blogs);
			mysqli_stmt_bind_result($stmt_blogs, $blog_title);
			
			$result = mysqli_stmt_get_result($stmt_blogs);
			$row = mysqli_fetch_array($result, MYSQLI_NUM);	
			
			$result_arr[] = array(
				'blog_id' => $blog_id,
				'title' => $row[0],
				'comments' => array()
			);
		}
		$result_arr[array_key_last($result_arr)]['comments'][] = array(
			'name' => $comment_name,
			'email' => $comment_email,
			'body' => $comment_body
		);
	}
	
	// disconnect DB
	close_db($conn);
	
	#echo "<pre>"; print_r($result_arr); echo "</pre>";
	
	// output as JSON
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result_arr);
}

?>