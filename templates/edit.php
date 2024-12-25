<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="/index.php/apps/photoframe/<?php echo $_['frame']->getId(); ?>" method="post">
    <h2>Edit <?php echo $_['frame']->getName(); ?></h2>

    <?php echo $this->inc('_fields.inc'); ?>

    <button type="submit">Update frame</button>
  </form>
</div>
