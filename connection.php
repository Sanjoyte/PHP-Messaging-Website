<?php
    $db_server   = 'localhost';
    $db_username = 'root'; 
    $db_password = '';
    $db_name     = 'project';

    // Create connection
    $connection = mysqli_connect($db_server, $db_username, $db_password, $db_name);

    // Check connection
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    } 
    
?>
