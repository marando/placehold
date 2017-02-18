<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
</head>
<body>

    <?php
    require 'vendor/autoload.php';
    use \Marando\Placehold\Placehold;

    ?>


  <img src="<?php echo new Placehold(250, 250, '#0195b7', '#f2fe6c', null,
    'Raleway-Regular') ?>">


</body>
</html>
