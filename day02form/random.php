<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Integer</title>
</head>
<body>
<form action="">
    Min: <input name="min" type="number"><br> <!--to accept decimal input, need to add step="any" in the input control -->
    Max: <input name="max" type="number"><br>
    <input type="submit" value="Generate random numbers">
    </form>
    <?php
    
    if (isset($_GET['min'])) {
        $min = $_GET['min'];
         $max = $_GET['max'];

       if (empty($min)|| empty($max)) {
           echo "please input integers on both min and max fields";
       }elseif($max<=$min){
            echo "Max must greater than mix";
       }else{
                   
             for ($i=0; $i < 10; $i++) { 
                
                    $randomNum = random_int($min,$max);
                       if ($i==9) {
                        echo "$randomNum";
                       }else{
                        echo "$randomNum, ";
                           }}     

       }
    }


    ?>
</body>
</html>