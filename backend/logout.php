<?php
session_start();
session_unset();    // Remove all session variables
session_destroy();  // Destroy the session entirely

// Go back to the login page (one folder up)
header("Location: ../auth.php");
exit();
?>