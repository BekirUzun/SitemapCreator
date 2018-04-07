<?php
	require_once '../dist/SitemapCreator.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$creator = new SitemapCreator();

		$sitemap = $creator->Create($_POST["start-url"], 60);

		if ($sitemap === false) {
			echo "sitemap oluşturulurken bir hata meydana geldi";
		}

		// creation succesful. lets print it
		Header('Content-type: text/xml');
		echo $sitemap;
		die();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Sitemap</title>
	<meta charset="utf-8">
</head>
<body>
	<h1>Sitemap Oluşturucu</h1>

	<form method="POST" action="">
		<input type="text" name="start-url">
		<input type="submit" value="Gönder">
	</form>

</body>
</html>