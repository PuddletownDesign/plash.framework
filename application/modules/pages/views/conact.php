<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?=$post->title?></title>

</head>
<body>
	<h1><?=$post->title?></h1>
	<?=$post->text?>
	
	<form action="#" method="post">
		<div>	
			<label for="user">Username</label><br>
			<input type="text" name="user" id="user" value=""><br><br>

			<label for="password">Password</label><br>
			<input type="text" name="password" id="password" value=""><br><br>

			<input type="hidden" name="login">
			<input type="submit" id="submit" value="submit">
		</div>
	</form>
</body>
</html>
