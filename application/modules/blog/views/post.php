<?php $this->inc('blog/head'); ?>
<meta name="description" content="<?=$post->description?>">
<title><?=$post->title?> | Blog | Plash Framework</title>
<?php $this->inc('blog/links'); ?>

	<div class="post">
<?php if ($user->priv > 3): ?>
		<div class="admin">
			<a href="<?=$post->url?>/edit" class="edit-button">Edit</a> 
			<a href="<?=$post->url?>/delete" class="delete-button">Delete</a>
		</div>
<?php endif; ?>
		
		<h1><?=$post->title?></h1>
				
		<div class="meta">
			Posted on: <em class="date"><?=$post->created?> </em>
			<!-- by: <em><a class="author" href="<?=Url::$rel?>about/<?=$post->author_url?>"><?=$post->author?></a></em> -->
<?php if ($post->updated): ?>
			<div><small>Last updated on: <em><?=$post->updated?> </em></small></div>
<?php endif; ?>
		</div>
		
		<p class="description"><em><?=$post->description?></em></p>
		
		<?=$post->text?>
<?php $this->inc('comments/list'); ?>

	</div>
			
<?php $this->inc('blog/footer'); ?>