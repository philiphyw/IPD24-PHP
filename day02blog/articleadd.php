<?php require_once 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article</title>
</head>
<body>



<?php
if (!isset($_SESSION['blogUser']) ) {
    die("Error:only authenticated users may post an article");
}

$userName =  $_SESSION['blogUser']['name'];
echo "<div>you are logged in as $userName <a href='./login.php'>  Log out</a></div>";
echo "<h1>Create New Article</h1>";


    function displayForm($title = "", $body = "")
    {
        // heredoc example
        $formLLL = <<< END
    <form method="post">
        Title: <input name="title" type="text" value="$title"><br>
        Body: <textarea name="body" rows="4" cols="150">$body</textarea><br>
        <input type="submit" value="Add Article">
    </form>
END;
        echo $formLLL;
    }

    if (isset($_POST['title'])) { // we're receving a submission
        $title = $_POST['title'];
        $body = $_POST['body'];
        // verify inputs
        $errorList = array();
        if (strlen($title) < 2 || strlen($title) > 100) {
            $errorList[] = "title must be 2-100 characters long";
            $title = "";
        }
        
        if (strlen($body) < 2 || strlen($body) > 4000) {
            $errorList[] = "body must be 2-4000 characters long";
            $body = "";
        }
    
        //
        if ($errorList) { // STATE 2: submission with errors (failed)
            echo '<ul class="errorMessage">';
            foreach ($errorList as $error) {
                echo "<li>$error</li>\n";
            }
            echo '</ul>';
            displayForm($title, $body);
        } else { // STATE 3: submission successful

            echo "The article of  $title bas been added.";
        }
    } else { // STATE 1: first show
        displayForm();
    }

?>

</body>
</html>