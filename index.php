<?php
require 'vendor/autoload.php';
use \Marando\Placehold\Placehold;

?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
</head>
<body>

    <?php
    $img =
      Placehold::make('jpeg')
        ->size(200, 300)
        ->fg("#000")
        ->bg("#bbb")
    ?>
  <img src="<?php echo $img ?>">


    <?php
    $img =
      Placehold::make()
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
      Placehold::make()
        ->size(300, 200)
        ->fg("inv")
        ->bg("rand")
        ->text("No Image")
        ->font('Roboto Condensed Bold')
        ->maxFont(0.618)

    ?>
  <img src="<?php echo $img ?>">

    <?php
    $img =
      Placehold::make()
        ->size(200, 200)
        ->bg("rand")
        ->maxFont(0.618)

    ?>
  <img src="<?php echo $img ?>">


  <img src="<?php echo Placehold::rand(200, 400) ?>">

  <img style="max-width:300px" src="<?php echo Placehold::rand()->bg('#444') ?>">


    <?php for ($i = 0; $i < 50; $i++) { ?>

      <img src="<?php echo Placehold::rand(200, 400) ?>">

    <?php } ?>

</body>
</html>
