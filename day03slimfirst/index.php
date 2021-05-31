<?php
//tell php where the libraries are when they're needed;
require_once 'vendor/autoload.php';

use Slim\Http\Request;
use Slim\Http\Response;


// config the link to db for slim

    DB::$dbName = 'day01people';
    DB::$password = 'Q@mx/bHn2JkfIgYw';
    DB::$user = 'day01people';
    DB::$host = 'localhost';
    DB::$port = 3333;

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails = true'
]];
$app = new \Slim\App($config);

// Fetch DI Container
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        'cache' => dirname(__FILE__) . '/tmplcache',
        'debug' => true, // This line should enable debug mode
    ]);
    //
    $view->getEnvironment()->addGlobal('test1','VALUE');
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};


// Define app routes
$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
});
//age:[0-9]+, without the +, the argument only accept 1 digital number. like 15 will end up with an error.
$app->get('/hello/{name}/{age:[0-9]+}', function ($request, $response, $args) {
    $name = $args['name'];
    $age = $args['age'];
    
    //indert data to database
    DB::insert('people',['name'=>$name, 'age'=>$age]);
    $insertId = DB::insertId();
    
//render a template hello.html.twig in the templates folder and return it
return $this->view->render($response,'hello.html.twig',['age'=>$age, 'name'=>$name,'insertId'=>$insertId]);
});

//state 1: display the form
$app->get('/addperson',function($request,$response,$args){
        return $this->view->render($response,'addperson.html.twig');
});

//state 2 & 3 : rece from submission
$app->post('/addperson',function(Request $request, Response $response, $args){
    $name = $request ->getParam('name');
    $age = $request ->getParam('age');

    //validation
    $errorList = [];
    if (strlen($name)<2||strlen($name)>100) {
        $name="";//if the name is invalid, clear it so the field will show empty when redisplay the form
        $errorList[]="Name must be 2-100 characters long";
    }
    if (filter_var($age,FILTER_VALIDATE_INT)===false || $age<0||$age>150) {
        $age='';
        $errorList[]="Age must be a numebr between 0 and 150";
    }
    if ($errorList) {//erros: show and redisplay the form
        $valueList = ['name'=> $name,'age'=>$age];
        return $this->view->render($response,"addperson.html.twig",['errorList'=>$errorList, 'vList'=>$valueList]);
    }else{//success
        DB::insert('people',['name'=>$name,'age'=>$age]);
        return $this->view->render($response,"addperson_success.html.twig");
    }

});

// Run app
$app->run();
