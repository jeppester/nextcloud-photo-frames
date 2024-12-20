<?php
declare(strict_types=1);
?>

<!DOCTYPE html>
<html>

<head>
  <style>
    :root {
      background-color: #222;
    }

    :root,
    body {
      margin: 0;
    }

    .photoFrame {
      width: 100vw;
      height: 100vh;
      background-image: url('/index.php/apps/photoframe/<?php echo $_['shareToken'] ?>/image');
      background-position: center center;
      background-repeat: no-repeat;
      background-size: contain;
    }
  </style>
</head>

<div class="photoFrame">
</div>

</html>
