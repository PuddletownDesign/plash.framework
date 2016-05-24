<?php $this->inc('pages/head'); ?>
<title><?=$post->title?> | Blog | Plash Framework</title>
<?php $this->inc('pages/links'); ?>

	<div class="post">
		<div class="admin">
<!-- 			<a href="<?=$post->url?>/edit" class="edit-button">Edit</a> 
 -->		</div>
		
		<h1><?=$post->title?></h1>
						
		<?=$post->text?>
		
		<?php if (isset($error)): ?>
			<div class="error"><?=$error?></div>
		<?php endif; ?>

			<form action="login" method="post" id="login">
				<div>	
					<label for="user">Username</label><br>
					<input type="text" name="user" id="user" value=""><br><br>

					<label for="password">Password</label><br>
					<input type="password" name="password" id="password" value=""><br><br>

					<input type="hidden" name="login">
					
					<div class="admin">
						<button id="submit" name="submit" class="submit-button">Login</button>
					</div>
				</div>
			</form>
	</div>

<?php $this->inc('pages/footer') ?>


