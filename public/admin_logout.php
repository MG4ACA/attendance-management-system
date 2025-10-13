<?php
require_once '../config/db.php';

// Clear all session data
session_destroy();

flash_message('You have been logged out successfully.');
redirect('index.php');
?>