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