<?php $this->inc('blog/head'); ?>
<title>Blog | Plash Framework | Mobile Development, Best Practices</title>
<?php $this->inc('blog/links'); ?>

	
	<div class="list">
<?php if ($user->priv > 4): ?>
		<div class="admin">
			<a href="new" class="new-button">Make a New Post</a>
<!-- 				<a href="edit" class="edit-button">Edit This Page</a>-->		
		</div>
<?php endif; ?>

<?php if (isset($posts->{0})): ?>
		<ol class="posts">
<?php foreach($posts as $post): ?>
			<li class="xfolkentry">
				<h2 class="taggedlink"><a href="http://<?=Url::$domain?>blog/<?=$post->url?>"><?=$post->title?></a></h2>
				<em class="date"><?=$post->created?></em>
				<p class="description"><?=$post->description?></p>
			</li>

<?php endforeach;?>	
		</ol>
		
		<div class="paging">
			<div class="page-nav">
<?php if ($paging->prev): ?>
	<a href="<?=$paging->prev?>" rel="prev" class="prev">Prev</a>
<?php endif; ?>
<?php if ($paging->next): ?>
				<a href="<?=$paging->url?><?=$paging->next?>" rel="next" class="next">Next</a>
<?php endif; ?>
			</div>
		</div>
<?php else: ?>
		<p>There aren&#8217;t any posts yet&hellip;</p>
<?php endif; ?>		
	</div>

<?php $this->inc('blog/footer'); ?>

		
