<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Connect to Database</title>
</head>
<body>
    <?php
    //user name password for database
    $dbUser = 'day01people';
    $dbPass = 'Q@mx/bHn2JkfIgYw';
    $dbName = 'day01people';
    $dbHost = 'localhost:3333';

    $link = @ mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);//put a @ in front of the command to suppress the default system warning if anything went wrong. since we've applied a customer checking/waring underneath

    if (mysqli_connect_errno()) {
        die("Fatal error: failed to connect to mySQL -".mysqli_connect_error());
    }else{
        echo "connected to database $dbName";
    }

    ?>
</body>
</html>