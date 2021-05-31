<?php
        
        $name=$_GET['name'];
        echo "Nice to meet you $name!";
        define("AUTHOR",'Philip Huang');
        $statement = "This is the start of PHP learning"
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php echo "Hello world from PHP";?>
<br>
<?php 

// echo "The statement is $statement";
// echo "The statement is ".$statement;
// echo strlen($statement);
// echo strtoupper($statement);
// echo str_replace('h','H',$statement);

// $pi = 3.14;
// echo $pi**3;
// echo floor($pi);
// echo ceil($pi);
// echo pi() - $pi;

// $ages = array(10,20,30,40);
// print_r($ages);
// $ages[1]=200;
// $ages[]=50;
// array_push($ages,60);
// print_r($ages);

// $salaries = [10000,30000,40000];
$staff = [
    ['name'=>"Jim", 'age'=>30],
    ['name'=>"Pam", 'age'=>31],
    ['name'=>"Michael", 'age'=>44],
    ['name'=>"Dweight", 'age'=>35],
    ['name'=>"Tobby", 'age'=>43],
    ['name'=>"John", 'age'=>73],
    ['name'=>"Kelly", 'age'=>23]

];
// $agesal = array_merge($ages,$staff);
// print_r($agesal);
// array_pop($agesal);
// print_r($agesal);

// foreach ($staff as $s) {
//     echo $s.'<br/>';
// }

$i=0;
while ($i < count($staff)) {?>
<ul>
<li>
<?php 
echo $staff[$i]["name"].' - '.$staff[$i]["age"];

if ($staff[$i]["age"]<40) {
    echo "younger than 40";
}elseif ($staff[$i]["age"]>60) {
    echo "senior than 60";
}else{
    echo "somewhere between 40 - 60";
}
echo '<br/>';
echo $i%2==0; // show 1 when it's true, show nothing when it's false
?>
</li>
</ul>
<?php $i++; }?>

<?php 
echo 5=='5';
echo 5==='5';
?>

</body>
</html>