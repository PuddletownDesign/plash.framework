<?php View::inc('pages/head'); ?>
<title><?=$post->title?> | Blog | Plash Framework</title>
<?php View::inc('pages/links'); ?>

	<div class="post">
		<div class="admin">
			<a href="<?=$post->url?>/edit" class="edit-button">Edit</a> 
			<a href="<?=$post->url?>/delete" class="delete-button">Delete</a>
		</div>
		
		<h1><?=$post->title?></h1>
				
		<p class="description"><em><?=$post->description?></em></p>
		
		<?=$post->text?>
	</div>

<?php View::inc('pages/footer') ?>