<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>New Post</title>

</head>
<body>
	<h1>Make a new User</h1>
	<a href="./">cancel</a>
	
	<form action="new" method="post" enctype="multipart/form-data">
		<div>

			<label for="user">Username</label><br>
<?php if (isset($post->user_error)): ?>
			<div class="error"><?=$post->user_error?></div>
<?php endif; ?>
			<input type="text" name="user" id="user"<?php if (isset($post->user)):?> value="<?=$post->user?>"<?php endif;?>>
			<div>(can only contain lowercase, numbers and dashes)</div>
			<br><br>
			
			<label for="name">Real Name</label><br>
<?php if (isset($post->name_error)): ?>
			<div class="error"><?=$post->name_error?></div>
<?php endif; ?>
			<input type="text" name="name" id="name"<?php if (isset($post->name)):?> value="<?=$post->name?>"<?php endif;?>><br><br>

			<label for="password">Password</label><br>
<?php if (isset($post->password_error)): ?>
			<div class="error"><?=$post->password_error?></div>
<?php endif; ?>
			<input type="password" id="password" name="password"><br><br>

			<label for="password_check">Type password again</label><br>
			<input type="password" id="password_check" name="password_check"><br><br>

			<label for="priv">User Privledges</label><br>
			<select name="priv" id="priv">
				<option value="1"<?php if (isset($post->priv)):?><?php if ($post->priv==1): ?>selected="selected"<?php endif; ?><?php endif;?>>Guest (1)</option>
				<option value="2"<?php if (isset($post->priv)):?><?php if ($post->priv==2): ?>selected="selected"<?php endif; ?><?php endif;?>>Author (2)</option>
				<option value="3"<?php if (isset($post->priv)):?><?php if ($post->priv==3): ?>selected="selected"<?php endif; ?><?php endif;?>>Client (3)</option>
				<option value="4"<?php if (isset($post->priv)):?><?php if ($post->priv==4): ?>selected="selected"<?php endif; ?><?php endif;?>>Moderator (4)</option>
				<option value="5"<?php if (isset($post->priv)):?><?php if ($post->priv==5): ?>selected="selected"<?php endif; ?><?php endif;?>>Administrator (5)</option>
			</select>
			<br><br>
			
			<input type="hidden" name="new">
			<input type="submit" id="submit" value="submit">
		</div>
	</form>
</body>
</html>
