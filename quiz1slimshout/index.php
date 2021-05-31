<?php
session_start(); // enable Sessions mechanism for the entire web application

require_once 'vendor/autoload.php';
// require_once 'util.php';
use Respect\Validation\Validator as Validator;

use Slim\Http\Request;
use Slim\Http\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/errors.log', Logger::ERROR));

 // local db
    DB::$dbName = 'quiz1slimshout';
    DB::$user = 'quiz1slimshout';
    DB::$password = '8*)EP5Y@3Bf9M]Mh';
    DB::$port = 3333;

//error handler

DB::$error_handler = 'db_error_handler'; // runs on mysql query errors
DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

function db_error_handler($params) {
    global $log;
    // log first
    $log->error("Database error: " . $params['error']);
    if (isset($params['query'])) {
        $log->error("SQL query: " . $params['query']);
    }
    // redirect
    header("Location: /internalerror");
    die;
}

use Slim\Http\UploadedFile;

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true
]];
$app = new \Slim\App($config);

// Fetch DI Container
$container = $app->getContainer();

// File upload directory
$container['upload_directory'] = __DIR__ . '/uploads';

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        'cache' => dirname(__FILE__) . '/tmplcache',
        'debug' => true, // This line should enable debug mode
    ]);
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

// All templates will be given userSession variable
$container['view']->getEnvironment()->addGlobal('userSession', $_SESSION['userSession'] ?? null );
$container['view']->getEnvironment()->addGlobal('flashMessage', getAndClearFlashMessage());


$passwordPepper = 'mmyb7oSAeXG9DTz2uFqu';


// Attach middleware that verifies only Admin can access /admin... URLs

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Function to check string starting 
// with given substring 
function startsWith($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 

$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $url = $request->getUri()->getPath();
    if (startsWith($url, "/admin")) {
        if (!isset($_SESSION['userSession'])) { // refuse if user not logged in AS ADMIN
            $response = $response->withStatus(403);
            return $this->view->render($response, 'admin/error_access_denied.html.twig');
        }
    }
    return $next($request, $response);
});


// these functions return TRUE on success and string describing an issue on failure
function verifyUserName($username) {
    if (preg_match('/^[a-z0-9_]{5,20}$/', $username) != 1) { // no match
        return "Username must be 5-20 characters long and consist of lowercase letters and digits and underscore.";
    }
    return TRUE;
}

function verifyPasswordQuailty($pass1, $pass2) {
    if ($pass1 != $pass2) {
        return "Passwords do not match";
    } else {
        /*
        // FIXME: figure out how to use case-sensitive regexps with Validator
        if (!Validator::length(6,100)->regex('/[A-Z]/')->validate($pass1)) {
            return "VALIDATOR. Password must be 6-100 characters long, "
                . "with at least one uppercase, one lowercase, and one digit in it";
        } */
        
        if ((strlen($pass1) < 5) || (strlen($pass1) > 100)
                || (preg_match("/[A-Z]/", $pass1) == FALSE )
                || (preg_match("/[a-z]/", $pass1) == FALSE )
                || (preg_match("/[0-9]/", $pass1) == FALSE )) {
            return "Password must be 5-100 characters long, "
                . "with at least one uppercase, one lowercase, and one digit in it";
        }
    }
    return TRUE;
}

// LOGIN / LOGOUT USING FLASH MESSAGES TO CONFIRM THE ACTION

function setFlashMessage($message) {
    $_SESSION['flashMessage'] = $message;
}

// returns empty string if no message, otherwise returns string with message and clears is
function getAndClearFlashMessage() {
    if (isset($_SESSION['flashMessage'])) {
        $message = $_SESSION['flashMessage'];
        unset($_SESSION['flashMessage']);
        return $message;
    }
    return "";
}

function moveUploadedFile($directory, UploadedFile $uploadedFile,$username) {
    // Avoid a serious security flaw - user must not be ablet o upload .php file and exploit our server
    $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
    
    if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
        return FALSE;
    }
    //$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = $username.'.'.$extension;
    // echo $filename;
    try {
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename); // FIXME catch exception on failure
    } catch (Exception $e) {
        // TODO: log the error message
        return FALSE;
    }
    return $filename;
}

// returns TRUE on success
// returns a string with error message on failure
function verifyUploadedPhoto(Psr\Http\Message\UploadedFileInterface $photo, &$mime = null) {
    if ($photo->getError() != 0) {
        return "Error uploading photo " . $photo->getError();
    } 
    // if ($photo->getSize() > 1024*1024) { // 1MiB
    //     return "File too big. 1MB max is allowed.";
    // }
    $info = getimagesize($photo->file);
    if (!$info) {
        return "File is not an image";
    }
    // echo "\n\nimage info\n";
    // print_r($info);
    if ($info[0] < 50 || $info[0] > 500 || $info[1] < 50 || $info[1] > 500) {
        return "Image width and height must be within 50-500 pixels range";
    }
    $ext = "";
    switch ($info['mime']) {
        case 'image/jpeg': $ext = "jpg"; break;
        case 'image/gif': $ext = "gif"; break;
        case 'image/png': $ext = "png"; break;
        default:
            return "Only JPG, GIF and PNG file types are allowed";
    } 
    if (!is_null($mime)) {
        $mime = $info['mime'];
    }
    return TRUE;
}


// ============= URL HANDLERS BELOW THIS LINE ========================






// Define app routes
//Route - Internal Error
$app->get('/internalerror', function ($request, $response, $args) {
    return $this->view->render($response, 'error_internal.html.twig');
});


//Route - / (Index Page)
$app->get('/', function ($request, $response, $args) {
   
    
    return $response->withRedirect("/shouts/all");
   
});

//Route -Register
//  - username must be composed of 5-20 characters only lowercase letters and digits and underscore. Must not exist in the database yet (verify that on submission, no need for AJAX here).
//  - password must be composed of 5-100 characters with at least one uppercase, one lowercase and one digit, password must be encrypted for full marks
//  - image must be provided in one of jpg/gif/png formats, width and height must both be within 50 to 500 pixels range. Saved into 'uploads/' directory. Filename must be guaranteed to be unique. For full marks it must be based on username. E.g. user with username 'jerry123' would have its image in 'uploads/jerry123.jpg' if the type uploaded is jpg. Note that extension must be preserved based on the actual type of file uploaded by the user.
// Note: for full marks the password must be encrypted.

// STATE 1: first display
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/register', function ($request, $response, $args) {
    $username = $request->getParam('username');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();
    //
    $result = verifyUserName($username);
    if ($result !== TRUE) { $errorList[] = $result; }
    // verify image
    $hasPhoto = false;
    $mimeType = "";
    $uploadedImage = $request->getUploadedFiles()['image'];
    if ($uploadedImage->getError() != UPLOAD_ERR_NO_FILE) { // was anything uploaded?
        // print_r($uploadedImage->getError());
        $hasPhoto = true;
        $result = verifyUploadedPhoto($uploadedImage, $mimeType);
        if ($result !== TRUE) {
            $errorList[] = $result;
        } 
        
    }
    // verify username
    if (verifyUserName($username) === FALSE) {
        $errorList [] =  "Username does not look valid" ;
        $username = "";
    } else {
        // is username already in use?
        $record = DB::queryFirstRow("SELECT id FROM users WHERE username=%s", $username);
        if ($record) {
            array_push($errorList, "This username is already registered");
            $username = "";
        }
    }
    //
    $result = verifyPasswordQuailty($pass1, $pass2);
    if ($result !== TRUE) { $errorList[] = $result; }
    //
    if ($errorList) { // STATE 3: errors
        return $this->view->render($response, 'register.html.twig',
                [ 'errorList' => $errorList, 'v' => ['username' => $username]  ]);
    } else { // STATE 2: all good
        $photoData = null;
        $imagePath = null;
        if ($hasPhoto) {
            $photoData = file_get_contents($uploadedImage->file);
            $directory = $this->get('upload_directory');
            $uploadedImagePath = moveUploadedFile($directory, $uploadedImage,$username);
            $imagePath = $uploadedImagePath;
            if ($uploadedImagePath == FALSE) {
                return $response->withRedirect("/internalerror", 301);
            }
        }
        //
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $pass1, $passwordPepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT); // PASSWORD_ARGON2ID);
        DB::insert('users', ['username' => $username, 'password' => $pwdHashed,
                    'imagePath' => $imagePath]);
        return $this->view->render($response, 'register_success.html.twig');
    }
});


//Route - Login
// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use ($log) {
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT id,username,password,imagePath FROM users WHERE username=%s", $username);
    $loginSuccess = false;
    if ($record) {
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $password, $passwordPepper);
        $pwdHashed = $record['password'];
        if (password_verify($pwdPeppered, $pwdHashed)) {
            $loginSuccess = true;
        }
        // WARNING: only temporary solution to allow for old plain-text passwords to continue to work
        // Plain text passwords comparison
        else if ($record['password'] == $password) {
            $loginSuccess = true;
        }
    }
    //
    if (!$loginSuccess) {
        $log->info(sprintf("Login failed for username %s from %s", $username, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['userSession'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for username %s, uid=%d, from %s", $username, $record['id'], $_SERVER['REMOTE_ADDR']));
        $returnUrl = $request->getParam('returnUrl', "");
        // TODO: Sanitize URL - refuse to use it if invalid, e.g. check if it begins with '/'   /viewitem/234
        if ($returnUrl) {
            setFlashMessage("You have logged in");
            return $response->withRedirect($returnUrl, 301);
        } else {
            return $this->view->render($response, 'login_success.html.twig',
            ['userSession' => $_SESSION['userSession'], 'returnUrl' => $returnUrl ] );
        }     
    }
});

//Route - Logout
// STATE 1: first display
$app->get('/logout', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['userSession']['id'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['userSession']);
    setFlashMessage("You've been logged out");
    return $response->withRedirect("/");
});


//Route - shouts/add - 3 state form
//  - only accessible for authenticated users
//  - message must be 1-100 characters long, id is taken from session
//  - on successful submission add record to shouts and provide user with link to /shouts/list
//  - on failed submission display error message and re-display the form
// STATE 1: first display
$app->get('/shouts/add', function ($request, $response, $args) use ($log) {
    if (!isset($_SESSION['userSession'])) { // refuse if user not logged in
        $log->warning(sprintf("Access denied from %s", $_SERVER['REMOTE_ADDR']));
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $user = $_SESSION['userSession'];
    return $this->view->render($response, 'addarticle.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/shouts/add', function (Request $request, Response $response, $args) use ($log) {
    if (!isset($_SESSION['userSession'])) { // refuse if user not logged in
        $log->warning(sprintf("Access denied from %s", $_SERVER['REMOTE_ADDR']));
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $message = $request->getParam('message');

    $errorList = array();
    // EXAMPLE of Validator use from respect/validation package
    //if (!Validator::stringType()->length(2, 100)->alnum(' .:,/')->validate($title)) {
    if (strlen($message) < 1 || strlen($message) > 100) {
        array_push($errorList, "Message must be 1-100 characters long, alphanumeric characters only");
        // keep the title even if invalid
    }
    
    if ($errorList) {
        return $this->view->render($response, 'addarticle.html.twig',
                [ 'errorList' => $errorList, 'v' => ['message' => $message ]  ]);
    } else {
        $username = $_SESSION['userSession']['username'];
        $authorId = $_SESSION['userSession']['id'];
        DB::insert('shouts', ['authorId' => $authorId, 'message' => $message]);
        $articleId = DB::insertId();
        $log->debug(sprintf("Added shout with shoutID %d by %s from %s", $articleId,$username, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'addarticle_success.html.twig', ['id' => $articleId]);
    }
});


// Route - shouts/list
//  - accessible to both authenticated an non-authenticated users
//  - initially display list of all shouts in an html table, one of the columns must be user's avatar image, width set to 80 pixels. Column list: id of shout, author name, author avatar, message
//  - above the table provide link to /shouts/add with text "Add new shout" if user is authenticated, otherwise nothing.
//  - above the table provide drop-down (combo box) with one first empty entry and names of all users as other options. When a name is selected you will re-load the content of the table using Ajax $...load() function to only display shouts coming from that one particular user. If empty drop-down entry (the first one) is selected then load all shouts from all users again.
// Note: For full marks you must use jWuery for that. You should add event handler change() on combobox. Value can be obtained inside the event handler as this.value or $(this).val() depending on jQuery version. Value could be username or id, it is up to you (See more on that below)


$shoutsPerPage = 10;


//show all shouts


$app->get('/shouts/all', function ($request, $response, $args) {
  
    global $shoutsPerPage;
    $pageNo = $args['pageNo'] ?? 1;
    $articleList = DB::query("SELECT a.id, a.authorId, a.message, u.username, u.imagePath "
        . "FROM shouts as a, users as u WHERE a.authorId = u.id ORDER BY a.id DESC LIMIT %d OFFSET %d",
             $shoutsPerPage, ($pageNo - 1) * $shoutsPerPage);
    foreach ($articleList as &$article) {
        // format posted date
        // $datetime = strtotime($article['creationTS']);
        // $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        // $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['message']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['message'] = $bodyPreview;
        $imgFolder = '/uploads/';
        $imgPath=$imgFolder.$article['imagePath'];
        $article['fullimgPath']=$imgPath;
    }
    return $this->view->render($response, 'ajaxsinglepage.html.twig', [
            'list' => $articleList
        ]);
});

//show shout list for current user


$app->get('/shouts/list', function ($request, $response, $args) {
  
    //verify if any login user
    if (!isset($_SESSION['userSession'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
   
    $userID = $_SESSION['userSession']['id'];
    $articleList = DB::query("SELECT a.id, a.authorId, a.message, u.username, u.imagePath "
        . "FROM shouts as a, users as u WHERE a.authorId = u.id and u.id = $userID ORDER BY a.id");
    foreach ($articleList as &$article) {
        // format posted date
        // $datetime = strtotime($article['creationTS']);
        // $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        // $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['message']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['message'] = $bodyPreview;
        $imgFolder = '/uploads/';
        $imgPath=$imgFolder.$article['imagePath'];
        $article['fullimgPath']=$imgPath;
    }
    return $this->view->render($response, 'ajaxsinglepage.html.twig', [
            'list' => $articleList
        ]);
});

// Run app
$app->run();
