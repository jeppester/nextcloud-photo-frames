<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="/index.php/apps/photoframe" method="post">
    <h2>New photo frame</h2>

    <?php echo $this->inc('_fields.inc', ['frame' => ['']]); ?>

    <div class="flex actions">
      <div class="grow"></div>
      <a href="/index.php/apps/photoframe" class="button" type="submit">Back</a>
      <button type="submit" class="primary">Create frame</button>
    </div>
  </form>
</div>
