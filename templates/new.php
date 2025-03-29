<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="<?= $urlGenerator->linkToRoute('photo_frames.page.create') ?>" method="post">
    <h2>New photo frame</h2>

    <?php echo $this->inc('_fields.inc'); ?>

    <div class="flex actions">
      <div class="grow"></div>
      <a href="<?= $urlGenerator->linkToRoute('photo_frames.page.index') ?>" class="button" type="submit">Back</a>
      <button type="submit" class="primary">Create frame</button>
    </div>
  </form>
</div>