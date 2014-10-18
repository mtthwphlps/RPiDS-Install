<?php
if(file_exists('../config/servertype.php')) {
	require_once('../config/servertype.php');
	$run_install = true;
	if($server_type == 'admin') {
		$server_name = 'Admin Server';
	} elseif($server_type == 'cache') {
		$server_name = 'Cache Control Server';
	} elseif($server_type == 'display') {
		$server_name = 'Display Server';
	} elseif($server_type == 'central') {
		$server_name = 'Central Server';
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>RPiDS Server Install</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
</head>
<body>
<div id="container">
	<h1>RPiDS Install</h1>
	<h2>Welcome to RPiDS.</h2>
	<form id="didform" name="rpids_install">
		<?php if($run_install) { ?>
			<p>This system is pre-configured as <?php echo $server_name; ?>. The installation process will start immediately.</p>
			<input id="type" name="server_type" type="hidden" value="<?php echo $server_type; ?>">
			<script type="text/javascript">
				jQuery(document).ready(function() {
					setTimeout(function() {
						run_install();
					}, 1000);
				});
			</script>
		<?php } else { ?>
			<p>To start the install select what type of server this is.</p>
			<select id="type" name="server_type">
				<option value="">...</option>
				<option value="admin">Admin</option>
				<option value="cache">Cache/Control</option>
				<option value="display">Display</option>
			</select> 
			<input id="submit" name="Submit" type="button" value="submit" onClick="run_install();" />
		<?php } ?>
	</form>
	<span class="status"></span>
</div>
</body>
</html>