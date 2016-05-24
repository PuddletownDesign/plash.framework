<?php View::inc('blog/head'); ?>
<title><?=$post->title?> | Blog | Plash Framework</title>
<?php View::inc('blog/links'); ?>
	
	<form action="edit" method="post" class="post">
		<div class="admin">
			<a href="../<?=$post->url?>" class="cancel-button">Cancel Edit</a> 
			<a href="../<?=$post->url?>/delete" class="delete-button">Delete</a>
		</div>
		
		<h1><span class="mode">Edit: </span><?=$post->title?></h1>
		<em class="date">First Published on: <?=$post->created?></em>
		<div>
			<label for="description">Description</label><br>
			<textarea name="description" id="description" rows="8" cols="40"><?=$post->description?></textarea><br><br>
			
			<label for="text">Text</label><br>
<?php if (isset($post->text_error)): ?>
			<div class="error">You forgot to type a post&hellip;</div>
<?php endif; ?>
			<textarea name="text" id="text" rows="8" cols="40"><?=$post->text?></textarea><br><br>
			
<!-- 			<label for="tags">Tags</label><br>
			<input type="text" name="tags" id="tags" value="<?=$post->tags?>"><br><br> -->
			
<!-- 			<label for="category">Category</label><br>
			<input type="text" name="category" id="category" value="<?=$post->category?>"><br><br> -->

			<input type="hidden" name="edit" value="<?=$post->url?>">
			<input type="hidden" name="title" value="<?=$post->title?>">
	
			
			<div class="admin">
				<button id="submit" name="submit" class="submit-button">Submit</button>
				<a href="../<?=$post->url?>" class="cancel-button">Cancel Edit</a> 
			</div>
			
		</div>
	</form>
<?php View::inc('blog/footer') ?>
