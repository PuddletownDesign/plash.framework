<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>List of Plates in Garage</title>
</head>
<body>
	<h1>Users</h1>
	<table>
		<thead>
			<tr>
				<th scope='col'>Delete</th>
				<th scope='col'>Id</th>
				<th scope='col'>User</th>
				<th scope='col'>Real Name</th>
				<th scope='col'>Privledge</th>
			</tr>
		</thead>
		<tbody>
<?php foreach($users as $user): ?>
			<tr>
				<td></td>
				<td><?=$user->id?></td>
				<td><a href="../user/<?=$user->user?>"><?=$user->user?></a></td>
				<td><?=$user->name?></td>
				<td><?=$user->priv?></td>
			</tr>
<?php endforeach;?>
		</tbody>
	</table>
</body>
</html>
