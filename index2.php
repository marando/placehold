<?php
require 'vendor/autoload.php';
use \Marando\Placehold\Placehold;

for ($i = 0; $i <= 360; $i += 4) {
    $image = Placehold::png()
      ->size(80, 80)
      //->bgHSL($i, 50, 50)
      //->bgHSL($i, 67, 50)
      ->bgHSL(0, 4, 12)
      //  ->randFg();

      // H S L spread of random nexx 0-360 deg 0-100% S and L
      ->randBg([70, 100], [20, 30], [30, 40])
      ->randFg([0, 20], [50, 100], [50, 100]);
    //->randBg();
    //->text("{$i}&deg;");
    //->randBg();
    ?>

  <img src="<?= $image ?>">

<?php } ?>


