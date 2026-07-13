<?php
// starts session (if not already)
session_start();

// destroys session to log user out
session_destroy();

// sends user back to home
header("Location: index.php");

exit;