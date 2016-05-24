<?php View::inc('blog/head'); ?>
<title>Make a New Blog Post | Blog | Plash Framework</title>
<?php View::inc('blog/links'); ?>

	
	
	<form action="#" method="post" class="post">
		<div class="admin">
			<a href="./" class="cancel-button">Cancel</a> 
		</div>

		<h1>Make a new Blog Post</h1>
		
		<div>	
			<label for="title">Title <span class="required">&#9733;</span></label><br>
<?php if (isset($post->title_error)): ?>
			<div class="error"><?=$post->title_error?></div>
<?php endif; ?>
			<input type="text" name="title" id="title"<?php if (isset($post->title)):?> value="<?=$post->title?>"<?php endif;?>><br><br>

<label for="description">Description</label><br>
<textarea name="description" id="description" rows="8" cols="40"><?php if (isset($post->description)):?><?=$post->description?><?php endif;?></textarea><br><br>


			<label for="text">Text <span class="required">&#9733;</span></label><br>
<?php if (isset($post->text_error)): ?>
			<div class="error"><?=$post->text_error?></div>
<?php endif; ?>
			<textarea name="text" id="text" rows="8" cols="40"><?php if (isset($post->text)):?><?=$post->text?><?php endif;?></textarea><br><br>

<!-- 			
			<label for="tags">Tags</label><br>
			<input type="text" name="tags" id="tags"<?php if (isset($post->tags)):?> value="<?=$post->tags?>"<?php endif;?>><br><br>

			<label for="category">Category</label><br>
			<input type="text" name="category" id="category"><br><br>
 -->
			
			<div class="admin">
				<input type="hidden" name="new">
				<button class="new-button" id="new-button" name="new-button">Publish New Post</button>
				<a href="./" class="cancel-button">Cancel</a>
			</div>
		</div>
	</form>

<?php View::inc('blog/footer') ?>
