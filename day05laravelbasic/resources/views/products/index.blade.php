<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>Products Homepage</h1>
    <p>{{ $title }}</p>


    {{-- show product array by foreach loop through array variable --}}
    <ul>
    @foreach ($productArray as $product )
        <li>{{ $product['id'] }} -- {{$product['name']}}</li>
    @endforeach
    </ul>
</body>
</html>