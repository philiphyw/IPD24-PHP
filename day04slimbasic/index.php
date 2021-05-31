<?php

use Slim\Http\Request;
use Slim\Http\Response;

require 'vendor/autoload.php';

$app = new \Slim\App();

$countries=array(
    array('name'=>'Canada'),
    array('name'=>'China'),
    array('name'=>'Dutch'),
    array('name'=>'Ethiopia'), 
);


$app->get('/',function($request,$response,$args){
   return  $response->write("welcome to the slim basic page yoo");
});


$app->get('/countries',function($request,$response,$args){
   return  $response->write("countries");
});

$app->run();