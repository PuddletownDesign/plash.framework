<xml version="1.0">
<rss version="2.0">
	<channel>
		<title>Plash Framework Blog Feed</title>
		<link>http://puddletowndesign.com</link>
		<description>Plash Blog feed covering mobile company products, industry news and mobile development best practices. </description>
		<language>en-us</language>
		<pubDate><?=$posts->{'0'}->created?></pubDate>
		<lastBuildDate><?=$posts->{'0'}->created?></lastBuildDate>
		<docs>http://puddletowndesign.com/blog/rss.xml</docs>

<?php foreach($posts as $post): ?>
		<item>
	         <title><?=$post->title?></title>
	         <link><?=URL::$abso?>blog/<?=$post->url?></link>
	         <description><?=$post->description?></description>
	         <pubDate><?=$post->created?></pubDate>
	         <guid><?=URL::$abso?>blog/<?=$post->url?></guid>
		</item>
		
<?php endforeach;?>    
	</channel>
</rss>