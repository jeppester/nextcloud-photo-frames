<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="<?= $urlGenerator->linkToRoute('photoframe.page.create') ?>" method="post">
    <h2>New photo frame</h2>

    <?php echo $this->inc('_fields.inc', ['frame' => ['']]); ?>

    <div class="flex actions">
      <div class="grow"></div>
      <a href="<?= $urlGenerator->linkToRoute('photoframe.page.index') ?>" class="button" type="submit">Back</a>
      <button type="submit" class="primary">Create frame</button>
    </div>
  </form>
</div>
