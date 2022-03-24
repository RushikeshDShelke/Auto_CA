<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css"/>
		<meta charset="UTF-8" name="viewport" content="width=device-width"/>
	</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<a class="navbar-brand" href="https://sourcecodester.com">Sourcecodester</a>
		</div>
	</nav>
	<div class="col-md-3"></div>
	<div class="col-md-6 well">
		<h3 class="text-primary">PHP - Simple Barcode Generator</h3>
		<hr style="border-top:1px dotted #ccc;"/>
		<div class="col-md-2"></div>
		<div class="col-md-8">
			<form method="POST">
				<div class="form-group">
					<label>Enter a text</label>
					<input type="text" class="form-control" name="barcode"/>
					<br />
					<center><button class="btn btn-primary" name="generate">Generate</button></center>
					<br />
					<?php include 'generate.php'?>
				</div>
			</form>
		</div>
	</div>
</body>
</html>