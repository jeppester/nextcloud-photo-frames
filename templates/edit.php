<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="<?= $urlGenerator->linkToRoute('photoframe.page.update', ["id" => $frame->getId()]) ?>" method="post">
    <h2>Edit <?php echo $_['frame']->getName(); ?></h2>

    <?php echo $this->inc('_fields.inc'); ?>

    <div class="flex actions">
      <div class="grow"></div>
      <a href="<?= $urlGenerator->linkToRoute('photoframe.page.index') ?>" class="button" type="submit">Back</a>
      <button type="submit" class="primary">Update frame</button>
    </div>
  </form>
</div>
