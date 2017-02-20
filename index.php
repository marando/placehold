<?php
require 'vendor/autoload.php';
use Marando\Color\Color;
use \Marando\Placehold\Placehold;

?>

<body style="background: #000; color: #fff">

  <img src="<?= Placehold::png()->size(140, 190)->bg('#834') ?>">


  <img src="<?= Placehold::jpg(20)->sizeRand(120, 200) ?>">


  <img src="<?= Placehold::png(9)->rand() ?>">

  <img src="<?= Placehold::png(9)->rand()->size(120, 200) ?>">

  <br><br>
    <?php for ($i = 0; $i < 12; $i++) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->sizeRand(240, 120)
        ->bgRand('hsl(0, 2%, 4%)', 'hsl(4, 12%, 24%)')
        ->text(function ($e) {
            return $e->bgColor;

            return sprintf(
              'hsl(%d, %d%%, %d%%)',
              $e->bgColor->h,
              $e->bgColor->s * 100,
              $e->bgColor->l * 100
            );
        });
      ?>">
    <?php } ?>

  <br><br>
    <?php for ($i = 0; $i < 12; $i++) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->sizeRand(240, 120)
        ->bgRand()
        ->text(function ($e) {
            return $e->bgColor;

            return sprintf(
              'hsl(%d, %d%%, %d%%)',
              $e->bgColor->h,
              $e->bgColor->s * 100,
              $e->bgColor->l * 100
            );
        });
      ?>">
    <?php } ?>

  <br><br>
    <?php for ($i = 0; $i < 12; $i++) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->sizeRand(240, 120)
        ->bgRand();
      ?>">
    <?php } ?>


  <br><br>
    <?php for ($i = 0; $i < 12; $i++) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->size(120, 120)
        ->bgRand()
        ->font('Roboto Condensed Light')
        ->text(function ($e) {
            return $e->bgColor;
        });
      ?>">
    <?php } ?>

  <br><br>
    <?php for ($i = 0; $i < 360; $i += 45) { ?>
      <img src="<?=
      $img = Placehold::png()
        ->size(250, 90)
        ->bg("hsl({$i}, 50%, 50%)")
        ->font('Roboto Condensed 400')
        ->text(function ($e) {
            return sprintf(
              'hsl(%d, %d%%, %d%%)',
              $e->bgColor->h,
              $e->bgColor->s * 100,
              $e->bgColor->l * 100
            );
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


  <br><br><br><br>
</body>
