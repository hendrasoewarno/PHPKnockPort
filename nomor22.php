<?php
include "library.php";
try {
	allow(get_client_ip(), "22");
	echo "Connect from " . get_client_ip() . " to port 22 Allowed";
} catch (Exception $e) {
	echo "Failed!";
}
?>
