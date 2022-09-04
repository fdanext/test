<?php

require('db.php');

// size of SQL part
$max_insert = 150;

// counters
$counter_posts = 0;
$counter_comments = 0;

// connect DB
$conn = connect_db();

// get data posts
$response = get_web_page("https://jsonplaceholder.typicode.com/posts");
$posts = json_decode($response);
#echo "<pre>"; print_r($posts); echo "</pre>";

// prepare SQL
$sql_insert_users = "INSERT INTO users (id) VALUES %s ON DUPLICATE KEY UPDATE id=id";
$sql_insert_blogs = "INSERT INTO blogs (id, user_id, title, body) VALUES %s ON DUPLICATE KEY UPDATE id=id";

$bind_users = '(?)';
$bind_blogs = '(?, ?, ?, ?)';

$values_users = array();
$values_blogs = array();

// write data
foreach ($posts as $post) {
	$id = $post->id;
	$user_id = $post->userId;
	$title = $post->title;
	$body = $post->body;
	
    array_push($values_users, $user_id);
	
	$values = array($id, $user_id, $title, $body);
    array_push($values_blogs, ...$values);
	
	$counter_posts++;
	
	// write part = $max_insert
	if ($counter_posts % $max_insert == 0) {
		insert_join($conn, $sql_insert_users, $max_insert, $bind_users, $values_users);
		insert_join($conn, $sql_insert_blogs, $max_insert, $bind_blogs, $values_blogs);
		$values_users = array();
		$values_blogs = array();
	}
}

// write last data
$last = $counter_posts % $max_insert;
if ($last != 0) {
	insert_join($conn, $sql_insert_users, $last, $bind_users, $values_users);
	insert_join($conn, $sql_insert_blogs, $last, $bind_blogs, $values_blogs);
}


// get data comments
$response = get_web_page("https://jsonplaceholder.typicode.com/comments");
$comments = json_decode($response);
#echo "<pre>"; print_r($comments); echo "</pre>";

// prepare SQL
$sql_insert_comments = "INSERT INTO comments (id, blog_id, name, email, body) VALUES %s ON DUPLICATE KEY UPDATE id=id";
$bind_comments = '(?, ?, ?, ?, ?)';
$values_comments = array();

// write data
foreach ($comments as $comment) {
	$id = $comment->id;
	$post_id = $comment->postId;
	$name = $comment->name;
	$email = $comment->email;
	$body = $comment->body;
	
	$values = array($id, $post_id, $name, $email, $body);
    array_push($values_comments, ...$values);
	
	$counter_comments++;
	
	// write part = $max_insert
	if ($counter_comments % $max_insert == 0) {
		insert_join($conn, $sql_insert_comments, $max_insert, $bind_comments, $values_comments);
		$values_comments = array();
	}
}

// write last data
$last = $counter_comments % $max_insert;
if ($last != 0) {
	insert_join($conn, $sql_insert_comments, $last, $bind_comments, $values_comments);
}

// disconnect DB
close_db($conn);


// output counters
$output = sprintf("Загружено %s записей и %s комментариев.", $counter_posts, $counter_comments);
console($output);


function get_web_page($url) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    ); 

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content = curl_exec($ch);

    curl_close($ch);

    return $content;
}

function insert_join($conn, $sql_insert, $max_insert, $binds, $values) {
	$binds = array_fill(0, $max_insert, $binds);
	$binds = implode(", ", $binds);
	$sql_insert = sprintf($sql_insert, $binds);
	$stmt_blogs = mysqli_prepare($conn, $sql_insert);
	
	$types = str_repeat('s', count($values));
	mysqli_stmt_bind_param($stmt_blogs, $types, ...$values);
	mysqli_stmt_execute($stmt_blogs);
}

function console($data) {
	$output = '<script>console.log("' . $data . '");</script>';
	echo $output;
}

?>