<?php $this->inc('blog/head'); ?>
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
<?php if ($post->updated): ?>
			<div>last updated on: <em><?=$post->updated?> </em></div>
<?php endif; ?>
		</div>
		
		<p class="description"><em><?=$post->description?></em></p>
		
		
		<?=$post->text?>
		
	</div>
	
<!-- 	<div class="comments">
<?php if (isset($comments->{0})): ?>
		<ol>
<?php foreach($comments as $comment): ?>
			<li>
				<h3><?=$comment->name?> Wrote at: <?=$comment->created?></h3>
				
				<?=$comment->text?>
			
			</li>
			
<?php endforeach;?>
		</ol>
<?php endif; ?>
	</div> -->
		
<!-- 		
		<form action="#" method="post" enctype="multipart/form-data" class="comment-form">
			<div>	
				<h2>Leave a Comment</h2>
				
				<label for="name">Name</label><br>
				<input type="text" name="name" id="name" value=""><br><br>

				<label for="email">Email</label><br>
				<input type="text" name="email" id="email" value=""><br><br>

				<label for="comment">Comment</label><br>
				<textarea name="comment" id="comment" rows="8" cols="40"></textarea><br><br>
				
				<input type="hidden" name="new-comment" value="<?=$post->id?>">
				<input type="submit" id="submit" value="submit">
			</div>
		</form> 
-->
<?php $this->inc('blog/footer') ?>