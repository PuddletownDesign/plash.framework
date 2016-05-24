<script src="<?=Url::$rel?>js/lib/jquery.js" type="text/javascript"></script>
<script src="<?=Url::$rel?>js/app/blog/init.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?=URL::$rel?>css/screen/screen.css" type="text/css" media="all">
</head>
<body>
<div id="wrapper" class="blog">
	<div class="head">
		<div class="meta">
			<strong class="site"><a href="<?=Url::$rel?>">Plash Framework</a></strong>
			<span class="section"><a href="<?=Url::$rel?>blog/">Blog</a></span>
		</div>
		
<?php if (isset($user->user)): ?>
		<div class="session">
			Logged in as: <span class="user"><a href="<?=Url::$rel?>user/<?=$user->user?>"><?=$user->name?></a> <small>(<a href="<?=Url::$rel?>logout">logout</a>)</small></span>
		</div>
<?php endif; ?>	
	</div>