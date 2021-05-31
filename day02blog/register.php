<?php require './db.php'; ?>
<?php




echo "<h1>New User Registration</h1>";


    function displayForm($name = "",$email = "",$password = "",$passwordrp = "", $errorList="") {
        $nameErr = isset($errorList["name"])?$errorList["name"]:"";
        $emailErr = isset($errorList["email"])?$errorList["email"]:"";
        $passwordErr = isset($errorList["password"])?$errorList["password"]:"";
        $passwordrpErr = isset($errorList["passwordrp"])?$errorList["passwordrp"]:"";
        $queryErr = isset($errorList["query"])?$errorList["query"]:"";

    echo '<form method="post">';
    echo "Your Name: <input name='name' type='text' value=$name><span class = 'errorMsg'>$nameErr</span><br/>";      
    echo "Your Email: <input name='email' type='email' value=$email><span class = 'errorMsg'>$emailErr</span><br/>";
    echo "Password: <input name='password' type='password' value=$password><span class = 'errorMsg'>$passwordErr</span><br/>";
    echo " Password(repeat): <input name='passwordrp' type='password' value=$passwordrp><span class = 'errorMsg'>$passwordrpErr</span><br/>";
    echo "<div class = 'errorMsg'>$queryErr</div>";
    echo "<input type='submit' name='register' value='Register!'>";
    echo "</form>";

    }   

    if (isset($_POST['register'])) {// we're receving a submission
        global $name ;
        global $email;
        global $password;
        global $passwordrp;
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $passwordrp = $_POST['passwordrp'];
        // verify name
        $errorList = array();
        if (strlen($name) < 2 || strlen($name) > 20) {
            $errorList['name']  ="Name must be 2-50 characters long";
        }
        //verify email
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 320) {
            $errorList['email']= "Email must be a valid email address and less than 320 characters long";    
        }

        //verify password
        if (strlen($password)<3||strlen($password) > 100) {
            $errorList['password'] ="Password must be 3 - 100 characters";
        }

        //verify password repeat match password
        if (strcmp (  $passwordrp ,  $password )!==0) {
            $errorList['passwordrp'] = "Password must match repeat password";
        }

        
         //check if the email has been registered
         $email = mysqli_real_escape_string($link, $email);
         $sql = "SELECT email FROM users WHERE email= '$email'";
         $result = mysqli_query($link, $sql);
         $userRecord = mysqli_fetch_assoc($result);
         if ($userRecord) {
         $errorList['email']= "Email has been registred, please choose a new email or login";     
         }
        

        if ($errorList) { // STATE 2: submission with errorList (failed)
            displayForm();
        } else { // STATE 3: 
                
                $name = mysqli_real_escape_string($link, $name);
                $email = mysqli_real_escape_string($link, $email);
                $password = mysqli_real_escape_string($link, $password);

               
            
                //insert new record to the database
                $name = mysqli_real_escape_string($link, $name);
                $email = mysqli_real_escape_string($link, $email);
                $password = mysqli_real_escape_string($link, $password);
                $sql ="INSERT INTO users(name,email,password) VALUES('$name','$email','$password')";
                
                //run the query and return error msg if there's any
                if (!mysqli_query($link, $sql)) {
                    $errorList['query'] ="Query error: failed to add new user to the database";
                    displayForm();
                }else{

                    
                    $_SESSION['email'] = $email;//section_start() has been called in db.php
                    header("location:./login.php");
                }
        }
    }else{
        displayForm();
    }
    

    
?>
