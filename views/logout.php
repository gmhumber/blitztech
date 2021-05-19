<?php

// Start or resume a session
session_start();

// Clear session data and destroy it, then redirect user to the login page
session_unset();
session_destroy();

header("Location: login.php");
exit();

?>