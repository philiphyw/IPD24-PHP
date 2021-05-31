<?php


    //user name password for database
    $dbUser = 'day02blog';
    $dbPass = '91[kUT8sAazfmoiG';
    $dbName = 'day02blog';
    $dbHost = 'localhost:3333';

    $link = @ mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);//put a @ in front of the command to suppress the default system warning if anything went wrong. since we've applied a customer checking/waring underneath

    if (mysqli_connect_errno()) {
        die("Fatal error: failed to connect to mySQL -".mysqli_connect_error());
    }else{
        // echo "connected to database $dbName";


    }

    if (!isset($_SESSION)) {
        session_start();
    }

    ?>