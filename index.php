<?php
require 'vendor/autoload.php';
use \Marando\Placehold\Placehold;
use \Marando\Placehold\Placehold2;

?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
</head>
<body>

    <?php
    $img =
      Placehold2::make('jpeg')
        ->size(200, 300)
        ->fg("#000")
        ->bg("#bbb")
    ?>
  <img src="<?php echo $img ?>">


    <?php
    $img =
      Placehold2::make()
        ->size(300, 200)
        ->fg("#bbb")
        ->bg("#000")
        ->font('Raleway Bold')
        ->text("No Image")
        ->maxFont(0.618)

    ?>
  <img src="<?php echo $img ?>">


    <?php
    $img =
      Placehold2::make()
        ->size(300, 200)
        ->fg("inv")
        ->bg("rand")
        ->font('Raleway Bold')
        ->text("No Image")
        ->maxFont(0.618)

    ?>
  <img src="<?php echo $img ?>">

    <?php
    $img =
      Placehold2::make()
        ->size(200, 200)
        ->bg("rand")
        ->font('Raleway Bold')
        ->maxFont(0.618)

    ?>
  <img src="<?php echo $img ?>">


  <img src="<?php echo Placehold2::rand(200, 400) ?>">

  <img style="max-width:300px" src="<?php echo Placehold2::rand()->bg('#444') ?>">

</body>
</html>
