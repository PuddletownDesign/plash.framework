<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Edit <?=$post->title?></title>

</head>
<body>
	<form action="edit" method="post">
		<div>	
			<label for="title">title</label><br>
			<input type="text" name="title" id="title" value="<?=$post->title?>"><br><br>

			<label for="text">text</label><br>
			<textarea name="text" id="text" rows="8" cols="40"><?=$post->text?></textarea><br><br>
			
			<label for="description">Description</label><br>
			<textarea name="description" id="description" rows="8" cols="40"><?=$post->description?></textarea><br><br>
			
			<label for="tags">tags</label><br>
			<input type="text" name="tags" id="tags" value="<?=$post->tags?>"><br><br>

			<input type="hidden" name="edit" value="<?=$post->url?>">
			<input type="submit" id="submit" value="submit">
		</div>
	</form>
</body>
</html>
