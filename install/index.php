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
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">
function run_install() {
	// Get the server type
	var type = jQuery("#type").val();
	// Run the install
	if(type != '') { // Provided value is not empty
		// Clear any error messages
		jQuery("#didform").removeClass("error");
		jQuery("span.status").removeClass("error");
		jQuery("span.status").html('<img src="img/ajax-loader.gif" alt="loading" /> Installing.');
		// Disable the form
		jQuery("#did").prop("disabled", true)
		jQuery("#submit").prop("disabled", true)
		var status = 'nothing was done';
		status = do_install(type, '1');
		console.log('Step 1: ' + status);
		if(status) {
			status = do_install(type, '2');
			console.log('Step 2: ' + status);
			if(status) {
				status = do_install(type, '3');
				console.log('Step 3: ' + status);
				if(status) {
					status = do_install(type, '4');
					console.log('Step 4: ' + status);
					if(status) {
						status = do_install(type, '5');
						console.log('Step 5: ' + status);
						jQuery("span.status").html('Install complete.');
					}
				}
			}
		}
	} else {
		/* Provided value isn't a number */
		jQuery("#didform").addClass("error");
		jQuery("span.status").addClass("error");
		jQuery("span.status").html("Server type not selected.");
	}
}
function do_install(type,step) {
	var status = '0';
	if(typeof type == 'undefined' || typeof step == 'undefined') {
		status = false;
	} else {
		// Do the install
		jQuery.ajax({
			url: "install.php?server_type=" + type + "&step=" + step,
			cache: false,
			async: false
		})
		.done(function(data) {
			var data = jQuery.parseJSON(data);
			console.log(data);
			if(data.status = '1') {
				jQuery("span.status").html('<img src="img/ajax-loader.gif" alt="loading" /> ' + data.message);
				status = true;
			} else {
				jQuery("span.status").html('<img src="img/ajax-loader.gif" alt="loading" /> ' + data.message);
				status = false;
			}
		});
	}
	return status;
}
</script>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}
body {
	background: #121212;
}
#container {
	width: 700px;
	margin: 10px auto;
	padding: 15px 15px 25px 15px;
	background: #FFFFFF;
	border: #333333 1px solid;
	border-radius: 10px;
	text-align: center;
	color: #000000;
}
#container h1 {
	margin: 5px 0 10px 0;
	font-size: 30px;
}
#container h2 {
	margin: 5px 0 20px 0;
	font-size: 20px;
}
#container p {
	margin: 5px 0;
}
#container form {
	margin: 0 0 15px 0;
}
#container form #type {
	width: 250px;
	background: #FFFFFF;
	border: #555555 1px solid;
	padding: 5px;
	font-size: 20px;
	border-radius: 5px;
}
#container form #submit {
	width: 100px;
	background: #FFFFFF;
	border: #555555 1px solid;
	font-size: 20px;
	padding: 5px;
	border-radius: 5px;
}
span.status {
	width: 100%;
	display: block;
}
span.error {
	color: #FF0000;
}
#container form.error #did, #container form.error #submit {
	background: #FFE1E1;
	border: #FF0000 1px solid;
}
</style>
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