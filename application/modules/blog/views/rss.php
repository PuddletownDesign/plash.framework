
<rss version="2.0">
	<channel>
		<title>Plash Framework Blog Feed</title>
		<link>http://puddletowndesign.com</link>
		<description>Plash Blog feed covering mobile company products, industry news and mobile development best practices. </description>
		<language>en-us</language>
		<pubDate><?=$posts->{'0'}->created?></pubDate>
		<lastBuildDate><?=$posts->{'0'}->created?></lastBuildDate>
		<docs>http://<?=Url::$domain?>blog/rss.xml</docs>

<?php foreach($posts as $post): ?>
		<item>
			<title><?=$post->title?></title>
			<link>http://<?=URL::$abso?>blog/<?=$post->url?></link>
			<description><?=$post->description?></description>
			<pubDate><?=$post->created?></pubDate>
			<guid>http://<?=Url::$domain?>blog/<?=$post->url?></guid>
		</item>

<?php endforeach;?>    
	</channel>
</rss>