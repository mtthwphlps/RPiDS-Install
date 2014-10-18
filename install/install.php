<?php
// Function to handle errors
$the_errors = '';
function process_error($error) {
	global $the_errors;
	$the_errors .= $error.'
';
}

// Function to handle non-error messages
$the_message = array();
function process_message($message) {
	global $the_message;
	if($message == false) {
		$the_message = array();
	} elseif($message == 'echo') {
		print_r($the_message);
	} elseif($message == 'return') {
		return $the_message;
	} else {
		$the_message[] = $message;
	}
}

// Function to handle feedback messages
$the_feedback = '';
function process_feedback($message) {
	global $the_feedback;
	if($message == false) {
		$the_feedback = '';
	} elseif($message == 'echo') {
		echo $the_feedback;
	} else {
		$the_feedback .= $message.'
';
	}
}

class rpids_updater {
	/*
		Private function: delete
		Delete all files and folders in a specified path
		Since: 2.0
	*/
	private function delete($path) {
		if(is_dir($path) == true) {
			$files = array_diff(scandir($path), array('.', '..'));
			process_message(''.$path.' is a directory.');
			foreach ($files as $file) {
				$this->delete(realpath($path) . '/' . $file);
			}
			process_message('Removing directory '.$path);
			return rmdir($path);
		} elseif(is_file($path) == true) {
			process_message('Removing file '.$path);
			return unlink($path);
		}
		return true;
	}
	
	/*
		Private function: copy
		Moves all files and folders in a specified path
		Since: 2.0
	*/
	private function copyfiles($source, $destination) {
		try {
			// Cycle through all source files
			if(is_dir($source) == true) {
				$files = array_diff(scandir($source), array('.', '..'));
				process_message('Copying directory '.$source.' to '.$destination);
				if(!file_exists($destination)) {
					if(!mkdir($destination, 0755)) {
						throw new Exception('Error making directory: '.$destination);
					}
					process_message('Created directory '.$destination);
				}
				foreach ($files as $file) {
					if($this->copyfiles(realpath($source).'/'.$file, realpath($destination).'/'.$file) == false) {
						throw new Exception('Error copying file: '.$destination);
					}
				}
			} elseif(is_file($source) == true) {
				if(!copy($source, $destination)) {
					throw new Exception('Error copying file: '.$destination);
				}
				process_message('Copied file '.$destination);
			}
		} catch (Exception $e) {
			process_error($e->getMessage());
			process_message($e->getMessage());
			return false;
		}
		return true;
	}
	/*
		Make sure we have the required functions
	*/
	public function check_req_func() {
		$status = '';
		if(is_callable('curl_init')) {
			$code = 1;
			$status .= 'curl_init IS available
';
		} else {
			$status .= 'curl_init IS NOT available
';
		}
		if($code == 1) {
			return true;
		} else {
			return $status;
		}
	}
	/*
		Check if the download folder is writable
		Since: 2.0
	*/
	public function check_writability() {
		try {
			// try to create a file in the download folder
			if(file_put_contents('download/test.txt', 'This is a test. This is only a test.') == false) {
				// Can't create the file, throw an error
				throw new Exception("Can't create the test file.");
			} else {
				// We created the file, now try removing it
				if(unlink('download/test.txt') == false) {
					// Can't remove the file, throw an error
					throw new Exception("Can't remove the test file.");
				} else {
					// Everything looks good
					return true;	
				}
			}
		} catch (Exception $e) {
			// We have a problem
			process_error($e->getMessage());
			return false;
		}
	}
	
	/*
		Download the zip to the download folder
		Since: 2.0
		https://github.com/mtthwphlps/RPiDS/archive/master.zip
	*/
	function get_update() {
		try {
			// Get the zip of the master branch and save to the download folder
			set_time_limit(0);
			$fp = fopen ('download/master.zip', 'w');
			$ch = curl_init('https://github.com/mtthwphlps/RPiDS/archive/master.zip');
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			$result = curl_exec($ch);
			$curlerror = curl_errno($ch);
			curl_close($ch);
			fclose($fp);
			// Check the status of CURL
			if($result == false) {
				throw new Exception('We had a problem with CURL: Error number '.$curlerror);
			}
			return true;
		} catch (Exception $e) {
			process_error($e->getMessage());
			return false;
		}
	}
	
	/*
		Get the downloaded file ready, and remove what we don't need.
		Since: 2.0
	*/
	public function prep_files() {
		try {
			// Make sure we have the server type
			if(!isset($_GET['server_type'])) {
				throw new Exception("Server type isn't set.");
			}

			// Get the server type
			$type = filter_input(INPUT_GET, 'server_type', FILTER_SANITIZE_SPECIAL_CHARS);
			
			// While we're at it, set the server type permanently
			$servertype = '<?php
$server_type = "'.$type.'";
?>';
			if(!file_exists(dirname(dirname(__FILE__)).'/config/')) {
				mkdir(dirname(dirname(__FILE__)).'/config/');
			}
			file_put_contents(dirname(dirname(__FILE__)).'/config/servertype.php', $servertype);

			// Extract the files from the zip
			$zip = new ZipArchive;
			$res = $zip->open(dirname(__FILE__).'/download/master.zip');
			if($res == TRUE) {
				// Create the ready folder
				if (!file_exists(dirname(__FILE__).'/download/extracted/')) {
					mkdir(dirname(__FILE__).'/download/extracted/', 0755);
				}
				$zip->extractTo(dirname(__FILE__).'/download/extracted/');
				$zip->close();
				process_message('Zip extracted.');
			} else {
				throw new Exception('We had a problem opening the zip file.');
			}
		
			// Figure out the folder we need
			if($type == 'admin') {
				$folder = 'Admin_Server';
			} elseif($type == 'cache') {
				$folder = 'Cache_Control_Server';
			} elseif($type == 'display') {
				$folder = 'Display';
			} elseif($type == 'central') {
				$folder = 'Central_Server';
			}
			if(@$folder == '') {
				throw new Exception("Folder isn't set, we can't continue.");
			}
			
			// Move the needed folder
			// Identify directories
			$source = dirname(__FILE__).'/download/extracted/RPiDS-master/'.$folder.'/';
			$destination = dirname(__FILE__).'/download/ready/';
			if($this->copyfiles($source, $destination) == false) {
				throw new Exception('Error while copying files.');
			}
			// Remove the unneeded folders and zip file
			if($this->delete(dirname(__FILE__).'/download/extracted/') == false) {
				throw new Exception("We had a problem removing the temporary directory.");
			}
			if($this->delete(dirname(__FILE__).'/download/master.zip') == false) {
				throw new Exception("We had a problem removing the zip file.");
			}
		} catch (Exception $e) {
			process_error($e->getMessage().' (Line '.$e->getLine().')');
			return false;
		}
		// All is good
		return true;
	}
	/*
		Do the install
		Since: 2.0
	*/
	public function do_install() {
		try {
			// Do the update
			process_message('DOING THE UPDATE...');
			// Identify directories
			$source = dirname(__FILE__).'/download/ready/';
			$destination = dirname(dirname(__FILE__)).'/';
			// Copy the files to the main folder
			if($this->copyfiles($source, $destination) == false) {
				throw new Exception('Error while copying upgrade files.');
			}
			// Empty the temp directory
			if($this->delete(dirname(__FILE__).'/download/ready/') == false) {
				throw new Exception("We had a problem removing the temporary directory.");
			}
		} catch (Exception $e) {
			process_error($e->getMessage().' (Line '.$e->getLine().')');
			return false;
		}
		// All is good
		return true;
	}
}

$rpids_updater = new rpids_updater;

// The various install steps
// Get the current step
$step = filter_input(INPUT_GET, 'step', FILTER_SANITIZE_SPECIAL_CHARS);

// Step 1: Required functions check
if($step == '1') {
	$status = $rpids_updater->check_req_func();
	if($status == true) {
		$return = array('status'=>'1', 'message'=>'All required functions are available.');
		process_message(false);
	} else {
		$return = array('status'=>'0', 'message'=>$status);
		process_message(false);
	}
}

// Step 2: Writability check
if($step == '2') {
	if($rpids_updater->check_writability()) {
		$return = array('status'=>'1', 'message'=>'Temp directory is writable.');
		process_message(false);
	} else {
		$return = array('status'=>'0', 'message'=>$the_errors);
		process_message(false);
	}
}

// Step 3: Download the zip
if($step == '3') {
	if($rpids_updater->get_update()) {
		$return = array('status'=>'1', 'message'=>'Install archive downloaded.');
		process_message(false);
	} else {
		$return = array('status'=>'0', 'message'=>$the_errors);
		process_message(false);
	}
}

// Step 4: Extract and move the files around
if($step == '4') {
	if($rpids_updater->prep_files()) {
		$return = array('status'=>'1', 'message'=>'Ready to install.');
		process_message(false);
	} else {
		$return = array('status'=>'0', 'message'=>$the_errors);
		process_message(false);
	}
}

// Step 5: Do the install
if($step == '5') {
	if($rpids_updater->do_install()) {
		$return = array('status'=>'1', 'message'=>'Install is done.');
		process_message(false);
	} else {
		$return = array('status'=>'0', 'message'=>$the_errors);
		process_message(false);
	}
}
$return['timestamp'] = time();
echo json_encode($return);
/*echo 'Errors: 
'.$the_errors;*/
?>