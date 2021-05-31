<?php

$name="Jim Helper";



//assign global variable's VALUE as an argument to local variable, udpate local variable value WON'T affect global variable
function printLocalVariable($name = "no name"){
    $name = 'local variable changed name';
    for ($i=0; $i < 3; $i++) { 
        print_r(($i+1).'. '."$name".'<br/>');
    }
}

printLocalVariable($name);
echo '<br/>';
echo $name;

echo '<br/>';

//declare the global annotation to reference to global variable object,  udpate local variable value WILL affect global variable
function printGlobalVariable($name = "no name"){
    global $name;
    $name = 'local variable in function changed global variable';
    for ($i=0; $i < 3; $i++) { 
        print_r(($i+1).'. '."$name".'<br/>');
    }
}

printGlobalVariable($name);
echo '<br/>';
echo $name;
echo '<br/>';
?>