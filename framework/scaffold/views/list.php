<?php $sort = $this->id; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Scaffolding <?=$this->table;?></title>
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
		<h1>Scaffolding &laquo; <span class="table"><?=$this->table;?></span></h1>
		<p><strong><a href="?new">Add a new entry</a></strong></p>
<?php if (isset($post[0][$this->id])): ?>
		<table id="scaffold">
			<thead>
				<tr>
					<th scope='col'></th>
					<th scope='col'></th>
<?php foreach($post[0] as $key => $value): ?>
		<?php if ($key == $sort): ?>
			<th scope='col' class="headerSortUp"><?php echo self::cleanColumnNames($key); ?></th>
		<?php else: ?>
			<th scope='col'><?php echo self::cleanColumnNames($key); ?></th>
		<?php endif; ?>
<?php endforeach;?>
				</tr>
			</thead>
			<tbody>	
<?php for($i=0; $i< count($post); $i++): ?>
				<tr>
					<td class="edit-button"><a href="?edit=<?php echo $post[$i][$this->id]; ?>">edit</a></td>
					<td class="delete-button"><a href="?delete=<?php echo $post[$i][$this->id]; ?>">delete</a></td>
<?php foreach($post[$i] as $key => $value): ?>
					<td><?php echo htmlspecialchars(substr_replace($value, "", 20, -1));?></td>
<?php endforeach;?>
				</tr>
<?php endfor;?>
			</tbody>
		</table>
<?php else: ?>
		<p>Nothing here yet...</p>
<?php endif; ?>
		
	</div>
</body>
</html>
