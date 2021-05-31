<?php

echo "<h1>PHP Basic yoo</h1>";


define("PROVINAL_TAX_RATE",0.15);

echo PROVINAL_TAX_RATE*5200,"<br>";

echo var_dump(PROVINAL_TAX_RATE),"<br>";

echo strtotime("next Monday"),"<br>";//return a timestamp format

echo date('Y-m-d h:i:sa',strtotime("now")),"<br>";//convert timestamp format to reable date format

//associated array
$assocArray = ['Jim'=>12, 'Tom'=>30];
//add element
print_r($assocArray);
echo "<br>";
$assocArray['May']=15;
$assocArray['Tonny']=35;
$assocArray['King']=56;
$assocArray['Jason']=45;
print_r($assocArray);
echo "<br>";
//delete element
array_splice($assocArray,1,2);//delete from(include) the 2nd item, delete 3 items in total
print_r($assocArray);
echo "<br>";

//search funciton, if found a matched value, return the first matched record's index/key, else return false
echo array_search(56,$assocArray),"<br>";//return bool(true)
echo var_dump(in_array(56,$assocArray)),"<br>";//return true
echo array_search(9999,$assocArray),"<br>";//return "" in echo/print_r
echo var_dump(array_search(9999,$assocArray)),"<br>";//return bool(false)

//perators
echo var_dump(5!=5.0),"<br>";
echo var_dump(5!==5.0),"<br>";

$num1=14;
$num2="13";

echo $num1 <=> $num2,"<br>";//return -1 if num1<num2, return 0 if num1==num2, return 1 if num1>num2;


//logic operator "&&" vs "and"
$result1 = 10>9 && 10>11;//return false to result1
$result2 = 10>9 and 10>11;//return true to result2, the operator 'and' has lower precedence than comparison operator, thus, result2 will be assigned the value for 10>9
echo var_dump($result1), "<br/>";
echo var_dump($result2), "<br/>";


//for loop
for ($i=0; $i < 5; $i++) { 
    echo $i."<br/>";
}

//foreach loop
foreach ($assocArray as $key => $value) {
    echo "$key's age is $value"."<br/>";
}

//false value
echo var_dump(''==false), "<br/>";
echo var_dump(""==false), "<br/>";
echo var_dump(0==false), "<br/>";
echo var_dump(0.00==false), "<br/>";
echo var_dump("0"==false), "<br/>";
echo var_dump(null==false), "<br/>";
echo var_dump("0.00"==false), "<br/>";// this one will return false. For a string, only "0" equals false, "0.00" equals true
echo var_dump("0.00"==true), "<br/>";
//however, below statements return true...PHP is a loose type script language..
echo var_dump("0"=="0.00"), "<br/>";
echo var_dump(0.00=="0.00"), "<br/>";