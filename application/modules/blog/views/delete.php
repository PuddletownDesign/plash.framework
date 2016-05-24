<?php View::inc('blog/head'); ?>
<title><?=$post->title?> | Blog | Plash Framework</title>
<?php View::inc('blog/links'); ?>

	<div class="post">
		<form action="delete" method="post">
			<div class="admin">	
				<input type="hidden" name="delete" value="<?=$post->url?>">
				<a href="../<?=$post->url?>/edit" class="cancel-button">Edit</a>
				
				<button class="delete-button">Yes! Delete this page</button>
				<a href="../<?=$post->url?>" class="new-button">No! Don&#8217;t Delete This Page</a>
			</div>
		</form>
		
		
		<h1><span class="mode">Delete:</span> <?=$post->title?></h1>
		
		<em class="date">Posted on: <?=$post->created?></em>
		
		<p class="description"><em><?=$post->description?></em></p>
		
		
		<?=$post->text?>
	</div>

<?php View::inc('blog/footer') ?>