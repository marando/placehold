<?php
require 'vendor/autoload.php';
use Marando\Color\Color;
use \Marando\Placehold\Placehold;

?>

<body style="background: #000; color: #fff">


  <img src="<?= Placehold::jpg(20)->sizeRand(120, 200) ?>">


  <img src="<?= Placehold::png(9)->rand() ?>">

  <img src="<?= Placehold::png(9)->rand()->size(120, 200) ?>">


    <?php exit; ?>

  <br><br>
    <?php for ($i = 0; $i < 12; $i++) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->size(120, 120)
        //->bg(['hsl', [180, 190], [0, 0.9], [0.33, 0.9]])
        ->font('Roboto Condensed Light')
        ->text(function ($e) {
            return $e->bgColor;
        });
      ?>">
    <?php } ?>

  <br><br>

  <img src="<?= Placehold::png()->bg('#f02'); ?>">
  <img src="<?= Placehold::png()->bg('hsl(90,90%,50%)'); ?>">
  <img src="<?= Placehold::png()->bg('rgb(240,100,50)'); ?>">


  <img src="<?=
  $img = Placehold::png()->bg('#f0f');
  ?>">

  <img src="<?=
  $img = Placehold::png()->fg('034');
  ?>">


  <img src="<?=
  $img = Placehold::png()
    ->bgRand('hsl(100,90%,90%)', 'hsl(90,30%,50%)')
    ->sizeRand(50, 100);;
  ?>">


  <img src="<?=
  $img = Placehold::png()->bgRand('#f00', '#020')->sizeRand(50, 100);;
  ?>">

  <img src="<?=
  $img = Placehold::png()->fgRand()->sizeRand(50, 100);
  ?>">

</body>
