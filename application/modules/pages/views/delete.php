<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?=$post->title?></title>

</head>
<body>
	<form action="#" method="post" enctype="multipart/form-data">
		<div>	
			<strong>Are you sure you want to delete this page?</strong>
			<input type="submit" id="submit" value="submit">
			<a href="../<?=$post->url?>">no</a>
		</div>
	</form>
	
	<h1><?=$post->title?></h1>
	<em><?=$post->created?></em>
	<p><?=$post->text?></p>
</body>
</html>
