<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Form</title>
</head>
<body>
    <form action="">
    Name: <input name="name" type="text"><br>
    Age: <input name="age" type="number"><br>
    <input type="submit" value="Say hello">
    </form>
    <?php
    
    if (isset($_GET['name'])) {
        $name = $_GET['name'];
    $age = $_GET['age'];
        echo "<p> Hi $name, you are $age y/o. </p>";
}
    


    ?>
    
</body>
</html>