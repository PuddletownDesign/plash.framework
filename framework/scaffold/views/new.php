<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Scaffolding &laquo; Insert a new entry</title>
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
		<h1>Scaffolding &laquo; Insert a new <span class="table"><?=$this->table;?></span> entry</h1>
		<p><strong><a href="./">back to list</a></strong></p>
<?php
//------------------------------------------------------
//               Datatyped Form Format
//------------------------------------------------------
if (isset($vaild['error'])) {
	$error = $vaild['error'];
	$post = $_POST;	
}

if ($meta): ?>
		<form action="./?new" method="post" enctype="multipart/form-data">
	<?php for($i=0; $i <count($meta); $i++): ?>
		<?php // variables
			$type = $meta[$i]['Type'];
			$field = $meta[$i]['Field'];
			$default = $meta[$i]['Default'];
			$key = $meta[$i]['Key'];
			$null = $meta[$i]['Null'];
		?>
		<?php 
		//data type int
		if (strstr($type, "tinyint(1)") AND $key != 'PRI'): ?>
				<div class="int">
					<label for="<?=$field;?>"><?php echo ucwords(str_replace('_', ' ', $field));?> <?php if ($null == "NO"){ ?> <small class="required">(required)</small> <?php } ?> </label><br>
					<select name="<?=$field;?>" id="<?=$field;?>">
						<?php if (isset($post)): ?>
							<?php if ($post[$field]==1): ?>
								<option value="1" selected="selected">Yes</option>
								<option value="0">No</option>
							<?php else: ?>
								<option value="1">Yes</option>
								<option value="0" selected="selected">No</option>
							<?php endif; ?>
						<?php else: ?>
							<option value="0" <?php if ($default==0): ?>selected="selected"<?php endif; ?>>No</option>
							<option value="1" <?php if ($default==1): ?>selected="selected"<?php endif; ?>>Yes</option>
						<?php endif; ?>
					</select>						
					<br><br>
				</div>	
		<?php 
		//data type int
		elseif (strstr($type, "int") AND $key != 'PRI'): ?>
			<?php if (isset($error[$field])): ?>
				<div class="int error">
			<?php else: ?>
				<div class="int">
			<?php endif; ?>
					<label for="<?=$field;?>"><?php echo ucwords(str_replace('_', ' ', $field));?> <?php if ($null == "NO"){ ?> <small class="required">(required)</small> <?php } ?> <br><small class="type"><?=$type;?></small></label><br>
					<input type="text" name="<?=$field;?>" id="<?=$field;?>" 
					
					<?php if (isset($post[$field])): ?>
							value="<?=$post[$field];?>"
					<?php else: ?>
							value="<?=$default;?>"
					<?php endif; ?>>
					
					<?php if (isset($error[$field])): ?>
							<strong class="error"><?=$error[$field];?></strong>
					<?php endif; ?>
					<br><br>
					
				</div>
		<?php endif; ?>
	
	
	
		<?php 
		//data type VARCHAR
		if (strstr($type, "varchar") AND $key != 'PRI'): ?>
			<?php if (isset($error[$field])): ?>
				<div class="varchar error">
			<?php else: ?>
				<div class="varchar">
			<?php endif; ?>
					<label for="<?=$field;?>"><?php echo self::cleanColumnNames($field);?> <?php if ($null == "NO"){ ?> <small class="required">(required)</small> <?php } ?> <br><small class="type"><?=$type;?></small></label>
					<br>
					<input type="text" name="<?=$field;?>" class="varchar" id="<?=$field;?>"
				
					<?php if (isset($post[$field])): ?>
						<?php if (isset($post[$field])): ?>
								value="<?=$post[$field];?>"
						<?php else: ?>
							<?php if ($default): ?>
									value="<?=$default;?>"
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>>
					
					<?php if (isset($error[$field])): ?>
							<strong class="error"><?=$error['title'];?></strong>
					<?php endif; ?>
					<br><br>
				</div>
		<?php endif; ?>
	
		
		
		<?php 
		//data type text
		
		if (strstr($type, "text") AND $key != 'PRI'): ?>
		   <?php if (isset($error[$field])): ?>
		   		<div class="text error">
		   <?php else: ?>
		   		<div class="text">
		   <?php endif; ?>
					<label for="<?=$field;?>"><?php echo self::cleanColumnNames($field);?> <?php if ($null == "NO"){ ?> <small class="required">(required)</small> <?php } ?> <br><small class="type"><?=$type;?></small></label><br>

					<?php if (isset($post[$field])): ?>
							<textarea name="<?=$field;?>" id="<?=$field;?>" rows="8" cols="40"><?=$post[$field];?></textarea>
					<?php else: ?>
						<?php if ($default): ?>
								<textarea name="<?=$field;?>" id="<?=$field;?>" rows="8" cols="40"><?=$default;?></textarea>
						<?php else: ?>
								<textarea name="<?=$field;?>" id="<?=$field;?>" rows="8" cols="40"></textarea>
						<?php endif; ?>
					
					<?php endif; ?>
		
					<?php if (isset($error[$field])): ?>
							<strong class="error"><?=$error[$field];?></strong>
					<?php endif; ?>
					<br><br>
				</div>
		<?php endif; ?>
	
	
	
		<?php 
		//data type datetime
		if (strstr($type, "datetime") AND $key != 'PRI'): ?>
				<?php if (isset($error[$field])): ?>
				   		<div class="datetime error">
				<?php else: ?>
				   		<div class="datetime">
				<?php endif; ?>
					<label for="<?=$field;?>"><?php echo self::cleanColumnNames($field);?> <?php if ($null == "NO"){ ?> <small class="required"> (required)</small> <?php } ?><br><small class="type"><?=$type;?></small></label><br>
					<input type="text" name="<?=$field;?>" class="varchar" id="<?=$field;?>" 
					<?php if (isset($post[$field])): ?>
							value="<?=$post[$field];?>"
					<?php else: ?>
						value="<?=date('Y-m-d G:i:s');?>"
					<?php endif; ?>>
					<?php if (isset($error[$field])): ?>
							<strong class="error"><?=$error[$field];?></strong>
					<?php endif; ?>
					<br><br>
				</div>
		<?php endif; ?>
	
	
	
	<?php endfor;?>
			<div class="submit-button">
				<input type="hidden" name="new">
				<input type="submit" id="submit" value="Insert">
			</div>
		</form>











<?php 
//------------------------------------------------------
//         Standard not datatyped Form
//------------------------------------------------------
else: ?>
not datatyped
<?php endif; ?>
<?php //echo "\n<pre>\n", print_r($post), "\n</pre>\n";?>
		
	</div>
</body>
</html>
