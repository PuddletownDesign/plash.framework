<div class="comments">
<?php if (isset($comments->{0})): ?>
	<h2>Comments</h2>
	<ol>
<?php foreach($comments as $comment): ?>
		<li>
			<h3><?=$comment->name?> Wrote at: <?=$comment->created?></h3>
			
			<?=$comment->text?>
		
		</li>
		
<?php endforeach;?>
	</ol>
<?php endif; ?>

	<h2>Leave a new Comment</h2>

	<form action="#" method="post" class="comment-form">
		<div>	
			
			<label for="name">Name</label><br>
			<input type="text" name="name" id="name" value=""><br><br>

			<label for="email">Email</label><br>
			<input type="text" name="email" id="email" value="">
			<br>
			<small>We will only send you email if someone responds to you</small>
			<br>
			<br>
			

			<label for="comment">Comment</label><br>
			<textarea name="comment" id="comment" rows="8" cols="40"></textarea><br><br>
			
			<input type="hidden" name="new-comment" value="<?=$post->id?>">
			<input type="submit" id="submit" value="submit" class="submit-button">
		</div>
	</form>
</div>