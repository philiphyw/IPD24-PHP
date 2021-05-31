<?php require './db.php'; ?>
<?php


echo "<h1>User login</h1>";


function displayForm($email = "", $password = "")
{
    // heredoc example
    $formLLL = <<< END
<form method="post">
    Email: <input name="email" type="text" value="$email"><br>
    Password: <input name="password" value ="$password"><br>
    <input type="submit" value="Login">
</form>
END;
    echo $formLLL;
}
if (isset($_POST['email'])) { // we're receving a submission
    $email = $_POST['email'];
    $password = $_POST['password'];
    // verify inputs
    //DO NOT use email and password as the query where condition, since in php it's case INSENSITIVE. which means password: 123abc = 123ABC.
    $result = mysqli_query($link, sprintf("SELECT * FROM users WHERE email='%s'",
        mysqli_real_escape_string($link, $email)));
    if (!$result) {
        echo "SQL Query failed: " . mysqli_error($link);
        exit;
    }
    $userRecord = mysqli_fetch_assoc($result);
    $loginSuccessful = false;
    if ($userRecord) {
        if ($userRecord['password'] == $password) {
                $loginSuccessful = true;
        }
    }
    //
    if (!$loginSuccessful) { // STATE 2: submission with errors (failed)
        echo '<p class="errorMessage">Invalid username or password</p>';
        displayForm();
    } else { // STATE 3: submission successful
        unset($userRecord['password']); // for safety reasons remove the password
        $_SESSION['blogUser'] = $userRecord;
        echo "<p>login successful</p>";
        echo '<p><a href="index.php">Click here to continue</a></p>';
    }
} else { // STATE 1: first show
    displayForm();
}


?>