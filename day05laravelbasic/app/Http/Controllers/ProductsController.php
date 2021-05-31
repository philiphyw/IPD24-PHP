<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(){
        $title = "phil first Laravel app";
        //compact method to send variable to view, the para name in compact method MUST be the same as the variable name
        return view('products.index',compact("title"));
    }

    public function springsyntaxindex(){
        return view('products.springsyntaxindex');
    }

    public function about(){
        // With method to send variable to view
        $varPassByWith = 'This variable was sent by With method';
        return view('products.about')->with('withVar',$varPassByWith);
        
    }

    public function productArray(){
       
        $title ='Products in an array';
       
        $productArray = [
            ['id'=>'001','name'=>'IPhone'],
            ['id'=>'002','name'=>'IPad Pro'],
            ['id'=>'003','name'=>'Sony MX4']
        ];

        return view('products.index',compact('title','productArray'));

    }
}
