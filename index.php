<?php

require('settings.php');

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Поиск по комментариям</title>
		<link rel="stylesheet" href="res/style.css">
		<script src="res/search.js"></script>
	</head>
	<body>

		<div class="search">
			<form action="." method="get">
				<div style="">Поиск по комментариям:</div>
				<div class="search_block">
					<div class="search_input">
						<input class="valid" type="text" name="query" placeholder="что ищем?" value="<?= isset($_GET['query']) ? $_GET['query'] : ''; ?>">
						<div class="search_hint">*минимум 3 символа</div>
					</div>
					<input id="search" type="button" value="найти">
				</div>
			</form>
		</div>
		
		<div id="message" class="hidden"></div>
		
		<div class="blog hidden">
			<div class="title"></div>
			<div class="comment">
				<div class="comment_user">
					<span>[ <a href=""></a> ]</span>
				</div>
				<div class="comment_body"></div>
			</div>
		</div>
		
		<div id="search_output"></div>
		
		<script>
<?php

echo 'const MIN_LENGTH = ' . MIN_LENGTH . ';';
echo PHP_EOL;

if (isset($_GET['query']) && strlen($_GET['query']) >= MIN_LENGTH) {
	echo 'document.addEventListener("DOMContentLoaded", function() {
		SendQuery("' . $_GET['query'] . '");
	});';
	echo PHP_EOL;
}

?>
		</script>
	</body>
</html>