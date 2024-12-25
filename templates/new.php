<?php
declare(strict_types=1);
?>

<div class="app-content">
  <form action="/index.php/apps/photoframe" method="post">
    <h2>New photo frame</h2>

    <?php print_unescaped($this->inc('_fields.inc', ['frame' => ['']])); ?>

    <button type="submit">Create frame</button>
  </form>
</div>
