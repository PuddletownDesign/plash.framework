<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Add a New Page</title>
</head>
<body>
	<h1>Make a new Post</h1>
	<a href="./">cancel</a>
	<form action="#" method="post" enctype="multipart/form-data">
		<div>	
			<label for="title">Title</label> &#9733;<br>
<?php if (isset($post->title_error)): ?>
			<div class="error"><?=$post->title_error?></div>
<?php endif; ?>
			<input type="text" name="title" id="title"<?php if (isset($post->title)):?> value="<?=$post->title?>"<?php endif;?>><br><br>
			
			<label for="text">Text</label> &#9733;<br>
<?php if (isset($post->text_error)): ?>
			<div class="error"><?=$post->text_error?></div>
<?php endif; ?>
			<textarea name="text" id="text" rows="8" cols="40"><?php if (isset($post->text)):?><?=$post->text?><?php endif;?></textarea><br><br>
			
			<label for="description">Description</label><br>
			<textarea name="description" id="description" rows="8" cols="40"><?php if (isset($post->description)):?><?=$post->description?><?php endif;?></textarea><br><br>
		
			<input type="hidden" name="new">
			<input type="submit" id="submit" value="submit">
		</div>
	</form>
</body>
</html>
