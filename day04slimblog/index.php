<?php
session_start(); // enable Sessions mechanism for the entire web application

require_once 'vendor/autoload.php';

use Respect\Validation\Validator as Validator;

use Slim\Http\Request;
use Slim\Http\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/errors.log', Logger::ERROR));
/*
if (strpos($_SERVER['HTTP_HOST'], "ipd24.ca") !== false) {
    // hosting on ipd24.com
    DB::$dbName = 'cp5003_teacher';
    DB::$user = 'cp5003_teacher';
    DB::$password = 'm2Y0OSjxO4Ba';
} else { // local computer
    DB::$dbName = 'day04slimblog';
    DB::$user = 'day04slimblog';
    DB::$password = 'Vc2x7LjvFdXNa3uu';
    DB::$port = 3333;
}
*/

if (strpos($_SERVER['HTTP_HOST'], "ipd24.ca") !== false) {
    // hosting on ipd24.com
    DB::$dbName = 'cp5003_philiphyw';
    DB::$user = 'cp5003_philiphyw';
    DB::$password = 'vgkuwACJyD2T';
} else { // local computer
    DB::$dbName = 'day02blog';
    DB::$user = 'day02blog';
    DB::$password = '91[kUT8sAazfmoiG';
    DB::$host = 'localhost';
    DB::$port = 3333;
}

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
        'cache' => dirname(__FILE__) . '/cache',
        'debug' => true, // This line should enable debug mode
    ]);
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

// All templates will be given userSession variable
$container['view']->getEnvironment()->addGlobal('userSession', $_SESSION['blogUser'] ?? null );
$container['view']->getEnvironment()->addGlobal('flashMessage', getAndClearFlashMessage());


$passwordPepper = 'mmyb7oSAeXG9DTz2uFqu';

// ============= URL HANDLERS BELOW THIS LINE ========================


$app->get('/internalerror', function ($request, $response, $args) {
    return $this->view->render($response, 'error_internal.html.twig');
});

// TODO: Define app routes
// Define app routes
$app->get('/', function ($request, $response, $args) {
    $articleList = DB::query("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
        . "FROM articles as a, users as u WHERE a.authorId = u.id ORDER BY a.id DESC");
    foreach ($articleList as &$article) {
        // format posted date
        $datetime = strtotime($article['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['body']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['body'] = $bodyPreview;
    }
    return $this->view->render($response, 'index.html.twig', ['list' => $articleList]);
    //print_r($articleList);
    //return $response->write("");
});



$articlesPerPage = 3;

// PAGINATION WITHOUT AJAX - JUST A-HREFs
$app->get('/paginated[/{pageNo:[0-9]+}]', function ($request, $response, $args) {
    global $articlesPerPage;
    $pageNo = $args['pageNo'] ?? 1;
    $articlesCount = DB::queryFirstField("SELECT COUNT(*) AS COUNT FROM articles");
    $articleList = DB::query("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
        . "FROM articles as a, users as u WHERE a.authorId = u.id ORDER BY a.id DESC LIMIT %d OFFSET %d",
             $articlesPerPage, ($pageNo - 1) * $articlesPerPage);
    foreach ($articleList as &$article) {
        // format posted date
        $datetime = strtotime($article['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['body']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['body'] = $bodyPreview;
    }
    $maxPages = ceil($articlesCount / $articlesPerPage);
    $prevNo = ($pageNo > 1) ? $pageNo-1 : '';
    $nextNo = ($pageNo < $maxPages) ? $pageNo+1 : '';
    return $this->view->render($response, 'paginated.html.twig', [
            'list' => $articleList,
            'maxPages' => $maxPages,
            'pageNo' => $pageNo,
            'prevNo' => $prevNo,
            'nextNo' => $nextNo
        ]);
});

// PAGINATION WITH AJAX
$app->get('/ajaxpaginated[/{pageNo:[0-9]+}]', function ($request, $response, $args) {
    global $articlesPerPage;
    $pageNo = $args['pageNo'] ?? 1;
    $articlesCount = DB::queryFirstField("SELECT COUNT(*) AS COUNT FROM articles");
    $maxPages = ceil($articlesCount / $articlesPerPage);
    return $this->view->render($response, 'ajaxpaginated.html.twig', [
            'maxPages' => $maxPages,
            'pageNo' => $pageNo,
        ]);
});

$app->get('/ajaxsinglepage/{pageNo:[0-9]+}', function ($request, $response, $args) {
    global $articlesPerPage;
    $pageNo = $args['pageNo'] ?? 1;
    $articleList = DB::query("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
        . "FROM articles as a, users as u WHERE a.authorId = u.id ORDER BY a.id DESC LIMIT %d OFFSET %d",
             $articlesPerPage, ($pageNo - 1) * $articlesPerPage);
    foreach ($articleList as &$article) {
        // format posted date
        $datetime = strtotime($article['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['body']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['body'] = $bodyPreview;
    }
    return $this->view->render($response, 'ajaxsinglepage.html.twig', [
            'list' => $articleList
        ]);
});

$app->map(['GET', 'POST'],'/article/{id:[0-9]+}', function ($request, $response, $args) {
    $articleId = $args['id'];
    // step 1: fetch article and author info
    $article = DB::queryFirstRow("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, a.imagePath, u.name "
            . "FROM articles as a, users as u WHERE a.authorId = u.id AND a.id = %d", $articleId);
    if (!$article) { // TODO: use Slim's default 404 page instead of our custom one
        $response = $response->withStatus(404);
        return $this->view->render($response, 'article_not_found.html.twig');
    }
    $datetime = strtotime($article['creationTS']);
    $postedDate = date('M d, Y \a\t H:i:s', $datetime );
    $article['postedDate'] = $postedDate;
    // step 2: handle comment submission if there is one
    if ($request->getMethod() == "POST" ) {
        // is user authenticated?
        if (!isset($_SESSION['blogUser'])) { // refuse if user not logged in
            $response = $response->withStatus(403);
            return $this->view->render($response, 'error_access_denied.html.twig');
        }
        $authorId = $_SESSION['blogUser']['id'];
        $body = $request->getParam('body');
        // TODO: we could check other things, like banned words
        if (strlen($body) > 0) { // FIXME: body length not verified, also not stripped of html tags
            DB::insert('comments', [
                'articleId' => $articleId,
                'authorId' => $authorId,
                'body' => $body
            ]);
        }
    }
    // step 3: fetch article comments
    $commentsList = DB::query("SELECT c.id, u.name as authorName, c.creationTS, c.body FROM comments c, users u "
                . "WHERE c.authorId=u.id AND c.articleId = %d ORDER BY c.id", $articleId);
    foreach ($commentsList as &$comment) {
        $datetime = strtotime($comment['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $comment['postedDate'] = $postedDate;
    }
    //
    return $this->view->render($response, 'article.html.twig', ['a' => $article, 'commentsList' => $commentsList]);
});

// STATE 1: first display
$app->get('/addarticle', function (Request $request, Response $response, $args) {
    if (!isset($_SESSION['blogUser'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    return $this->view->render($response, 'addarticle.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/addarticle', function (Request $request, Response $response, $args) {
    if (!isset($_SESSION['blogUser'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $title = $request->getParam('title');
    $body = $request->getParam('body');
    // FIXME: sanitize body - 1) only allow certain HTML tags, 2) make sure it is valid html
    // WARNING: If you forget to sanitize the body bad things may happen such as JavaScript injection
    $body = strip_tags($body, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
    //
    $errorList = array();
    // EXAMPLE of Validator use from respect/validation package
    //if (!Validator::stringType()->length(2, 100)->alnum(' .:,/')->validate($title)) {
    if (strlen($title) < 2 || strlen($title) > 100) {
        array_push($errorList, "Title must be 2-100 characters long, alphanumeric characters only");
        // keep the title even if invalid
    }
    // if (!Validator::stringType()->length(2, 10000)->validate($body)) {
    if (strlen($body) < 2 || strlen($body) > 10000) {
        array_push($errorList, "Body must be 2-10000 characters long");
        // keep the body even if invalid
    }
    // verify image
    $hasPhoto = false;
    $uploadedImage = $request->getUploadedFiles()['image'];
    if ($uploadedImage->getError() != UPLOAD_ERR_NO_FILE) { // was anything uploaded?
        // print_r($uploadedImage->getError());
        $hasPhoto = true;
        $result = verifyUploadedPhoto($uploadedImage);
        if ($result !== TRUE) {
            $errorList[] = $result;
        } 
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'addarticle.html.twig',
                [ 'errorList' => $errorList, 'v' => ['title' => $title, 'body' => $body ]  ]);
    } else {
        if ($hasPhoto) {
            $directory = $this->get('upload_directory');
            $uploadedImagePath = moveUploadedFile($directory, $uploadedImage);
        }
        $authorId = $_SESSION['blogUser']['id'];
        DB::insert('articles', ['authorId' => $authorId, 'title' => $title, 'body' => $body, 'imagePath' => $uploadedImagePath]);
        $articleId = DB::insertId();
        return $this->view->render($response, 'addarticle_success.html.twig', ['id' => $articleId]);
    }
});

// FIXME: this function should be allowed to fail, on moveTo or invalid extension
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    // FIXME: extension here has a security flaw - use can upload .php file and expoit our server
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// returns TRUE on success
// returns a string with error message on failure
function verifyUploadedPhoto(Psr\Http\Message\UploadedFileInterface $photo, &$mime = null) {
        if ($photo->getError() != 0) {
            return "Error uploading photo " . $photo->getError();
        } 
        if ($photo->getSize() > 1024*1024) { // 1MiB
            return "File too big. 1MB max is allowed.";
        }
        $info = getimagesize($photo->file);
        if (!$info) {
            return "File is not an image";
        }
        // echo "\n\nimage info\n";
        // print_r($info);
        if ($info[0] < 200 || $info[0] > 1000 || $info[1] < 200 || $info[1] > 1000) {
            return "Width and height must be within 200-1000 pixels range";
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


//
$app->get('/profile', function ($request, $response, $args) {
    if (!isset($_SESSION['blogUser'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $user = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE id=%d", $_SESSION['blogUser']['id']);
    return $this->view->render($response, 'profile.html.twig', ['user' => $user]);
});

// Warning: this returns binary data, not HTML
$app->get('/profile/image/{id:[0-9]+}', function ($request, $response, $args) {
    /* // OPTIONAL - depending on security levels
    if (!isset($_SESSION['blogUser'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    } */
    $user = DB::queryFirstRow("SELECT imageData,imageMimeType FROM users WHERE id=%d AND imageData IS NOT NULL", $args['id']);
    if (!$user) { // not found - FIXME
        return $response->withStatus(404);
    }
    $response->getBody()->write($user['imageData']);
    return $response->withHeader('Content-type', $user['imageMimeType']);
});


// STATE 1: first display
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/register', function ($request, $response, $args) {
    $name = $request->getParam('name');
    $email = $request->getParam('email');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();
    //
    $result = verifyUserName($name);
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
    // verify email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        $errorList [] =  "Email does not look valid" ;
        $email = "";
    } else {
        // is email already in use?
        $record = DB::queryFirstRow("SELECT id FROM users WHERE email=%s", $email);
        if ($record) {
            array_push($errorList, "This email is already registered");
            $email = "";
        }
    }
    //
    $result = verifyPasswordQuailty($pass1, $pass2);
    if ($result != TRUE) { $errorList[] = $result; }
    //
    if ($errorList) { // STATE 3: errors
        return $this->view->render($response, 'register.html.twig',
                [ 'errorList' => $errorList, 'v' => ['name' => $name, 'email' => $email ]  ]);
    } else { // STATE 2: all good
        $photoData = null;
        if ($hasPhoto) {
            $photoData = file_get_contents($uploadedImage->file);
        }
        //
        global $passwordPepper;
        $pwdPeppered = hash_hmac("sha256", $pass1, $passwordPepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT); // PASSWORD_ARGON2ID);
        DB::insert('users', ['name' => $name, 'email' => $email, 'password' => $pwdHashed,
                    'imageData' => $photoData, 'imageMimeType' => $mimeType]);
        return $this->view->render($response, 'register_success.html.twig');
    }
});

// used via AJAX
$app->get('/isemailtaken/[{email}]', function ($request, $response, $args) {
    $email = isset($args['email']) ? $args['email'] : "";
    $record = DB::queryFirstRow("SELECT id FROM users WHERE email=%s", $email);
    if ($record) {
        return $response->write("Email already in use");
    } else {
        return $response->write("");
    }
});

// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use ($log) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE email=%s", $email);
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
        $log->info(sprintf("Login failed for email %s from %s", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['blogUser'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email %s, uid=%d, from %s", $email, $record['id'], $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login_success.html.twig', ['userSession' => $_SESSION['blogUser'] ] );
    }
});

$app->get('/logout', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['blogUser']['id'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['blogUser']);
    return $this->view->render($response, 'logout.html.twig', ['userSession' => null ]);
});


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

// STATE 1: first display
$app->get('/login2', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login2', function ($request, $response, $args) use ($log) {
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE email=%s", $email);
    $loginSuccess = false;
    if ($record) {
        if ($record['password'] == $password) {
            $loginSuccess = true;
        }        
    }
    //
    if (!$loginSuccess) {
        $log->info(sprintf("Login failed for email %s from %s", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['blogUser'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email %s, uid=%d, from %s", $email, $record['id'], $_SERVER['REMOTE_ADDR']));
        setFlashMessage("Login successful");
        return $response->withRedirect("/");        
    }
});


// STATE 1: first display
$app->get('/logout2', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['blogUser']['id'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['blogUser']);
    setFlashMessage("You've been logged out");
    return $response->withRedirect("/");
});



$app->get('/session', function ($request, $response, $args) {
    echo "<pre>\n";
    print_r($_SESSION);
    return $response->write("");
});

// NOTE: $_SESSION or $_FILES work the same way as they did before

// ADMIN INTERFACE EXAMPLE CRUD OPERATIONS HANDLING

$app->get('/admin', function ($request, $response, $args) {
    $usersList = DB::query("SELECT * FROM users");
    return $this->view->render($response, 'admin/index.html.twig', ['usersList' => $usersList]);
});

$app->get('/admin/users/list', function ($request, $response, $args) {
    $usersList = DB::query("SELECT id,isAdmin,name,email,password FROM users");
    return $this->view->render($response, 'admin/users_list.html.twig', ['usersList' => $usersList]);
});


// STATE 1: first display
$app->get('/admin/users/{op:edit|add}[/{id:[0-9]+}]', function ($request, $response, $args) {
    // either op is add and id is not given OR op is edit and id must be given
    if ( ($args['op'] == 'add' && !empty($args['id'])) || ($args['op'] == 'edit' && empty($args['id'])) ) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'admin/not_found.html.twig');
    }
    if ($args['op'] == 'edit') {
        $user = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE id=%d", $args['id']);
        if (!$user) {
            $response = $response->withStatus(404);
            return $this->view->render($response, 'admin/not_found.html.twig');
        }
    } else {
        $user = [];
    }
    return $this->view->render($response, 'admin/users_addedit.html.twig', ['v' => $user, 'op' => $args['op']]);
});

// STATE 2&3: receiving submission
$app->post('/admin/users/{op:edit|add}[/{id:[0-9]+}]', function ($request, $response, $args) {
    $op = $args['op'];
    // either op is add and id is not given OR op is edit and id must be given
    if ( ($op == 'add' && !empty($args['id'])) || ($op == 'edit' && empty($args['id'])) ) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'admin/not_found.html.twig');
    }

    $name = $request->getParam('name');
    $isAdmin = $request->getParam('isAdmin') ?? '0';
    $email = $request->getParam('email');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();

    $result = verifyUserName($name);
    if ($result != TRUE) { $errorList[] = $result; }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        array_push($errorList, "Email does not look valid");
        $email = "";
    } else {
        // is email already in use BY ANOTHER ACCOUNT???
        if ($op == 'edit') {
            $record = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE email=%s AND id != %d", $email, $args['id'] );
        } else { // add has no id yet
            $record = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE email=%s", $email);
        }
        if ($record) {
            array_push($errorList, "This email is already registered");
            $email = "";
        }
    }
    // verify password always on add, and on edit/update only if it was given
    if ($op == 'add' || $pass1 != '') {
        $result = verifyPasswordQuailty($pass1, $pass2);
        if ($result != TRUE) { $errorList[] = $result; }
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'admin/users_addedit.html.twig',
                [ 'errorList' => $errorList, 'v' => ['name' => $name, 'email' => $email ]  ]);
    } else {
        if ($op == 'add') {
            DB::insert('users', ['name' => $name, 'email' => $email, 'password' => $pass1, 'isAdmin' => $isAdmin]);
            return $this->view->render($response, 'admin/users_addedit_success.html.twig', ['op' => $op ]);
        } else {
            $data = ['name' => $name, 'email' => $email, 'isAdmin' => $isAdmin];
            if ($pass1 != '') { // only update the password if it was provided
                $data['password'] = $pass1;
            }
            DB::update('users', $data, "id=%d", $args['id']);
            return $this->view->render($response, 'admin/users_addedit_success.html.twig', ['op' => $op ]);
        }
    }
});


// STATE 1: first display
$app->get('/admin/users/delete/{id:[0-9]+}', function ($request, $response, $args) {
    $user = DB::queryFirstRow("SELECT id,isAdmin,name,email,password FROM users WHERE id=%d", $args['id']);
    if (!$user) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'admin/not_found.html.twig');
    }
    return $this->view->render($response, 'admin/users_delete.html.twig', ['v' => $user] );
});

// STATE 1: first display
$app->post('/admin/users/delete/{id:[0-9]+}', function ($request, $response, $args) {
    DB::delete('users', "id=%d", $args['id']);
    return $this->view->render($response, 'admin/users_delete_success.html.twig' );
});

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
        if (!isset($_SESSION['blogUser']) || $_SESSION['blogUser']['isAdmin'] == 0) { // refuse if user not logged in AS ADMIN
            $response = $response->withStatus(403);
            return $this->view->render($response, 'admin/error_access_denied.html.twig');
        }
    }
    return $next($request, $response);
});


// these functions return TRUE on success and string describing an issue on failure
function verifyUserName($name) {
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $name) != 1) { // no match
        return "Name must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.";
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
        if ((strlen($pass1) < 6) || (strlen($pass1) > 100)
                || (preg_match("/[A-Z]/", $pass1) == FALSE )
                || (preg_match("/[a-z]/", $pass1) == FALSE )
                || (preg_match("/[0-9]/", $pass1) == FALSE )) {
            return "Password must be 6-100 characters long, "
                . "with at least one uppercase, one lowercase, and one digit in it";
        }
    }
    return TRUE;
}

// Run app
$app->run();
