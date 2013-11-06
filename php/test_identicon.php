<?php
    require_once('./identicon.php');
    $icon = new Identicon('maogm12@gmail.com');

    Header("Content-type: image/png");
    echo $icon->image(128, 128);
?>