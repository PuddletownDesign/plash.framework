<?php



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Delete entry #<?=$post['id'];?></title>
	<style type="text/css">
		<?php include $this->view_dir.'/styles/'.$this->style.'.php'; ?>
	</style>
<?php 
echo '
	<script type="text/javascript" src="'.Url::$rel.'js/lib/jquery.js"></script>
	<script type="text/javascript" src="'.Url::$rel.'js/lib/jquery.tablesorter.js"></script>'
; ?>
</head>
<body>
	<div id="wrapper">
		<h1>Scaffolding &laquo; Delete <span class="table"><?=$this->table;?></span> entry #<?=$post['id'];?></h1>
		<h2>Are you sure you want to delete this entry?</h2>
		<p><strong><a href="./">No Back to list</a></strong></p>
		<form action="./?delete=<?=$post[$this->id];?>" method="post">
			<div>	
				<input type="hidden" name="delete" value="<?=$post['id'];?>">
				<input type="submit" id="submit" value="Delete This Entry">
			</div>
			
			<?php foreach($post as $key => $value): ?>
				
				
<?php if ($key !=$this->id): ?>
			<div>
					<h2><?php echo htmlspecialchars($this->cleanColumnNames($key)); ?></h2><hr>
					<p><?php echo htmlspecialchars($value); ?></p>
			</div>
<?php endif; ?>
					
				

			<?php endforeach;?>
		</form>
		
		
	</div>
</body>
</html>
