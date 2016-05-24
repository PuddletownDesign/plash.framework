<link rel="alternate" type="application/rss+xml" title="Plash Framework Blog Feed" href="http://<?=Url::$domain?>/blog/rss" />
<!-- Public CSS -->
<link rel="stylesheet" href="<?=URL::$rel?>css/screen/screen.css" type="text/css" media="all">

<!-- Admin CSS -->
<link rel="stylesheet" href="<?=URL::$rel?>css/screen/admin.css" type="text/css" media="all">

<!-- Public Libs -->
<script src="<?=Url::$rel?>js/lib/jquery.js" type="text/javascript"></script>

<!-- Admin Libs -->
<script type="text/javascript" src="<?=Url::$rel?>js/lib/pagedown/Markdown.Converter.js"></script>
<script type="text/javascript" src="<?=Url::$rel?>js/lib/pagedown/Markdown.Editor.js"></script>

<!-- Public Scripts -->
<script src="<?=Url::$rel?>js/app/blog/init.js" type="text/javascript"></script>

<!-- Admin Scripts -->
<script src="<?=Url::$rel?>js/app/blog/admin.js" type="text/javascript"></script>

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