<?php declare(strict_types=1);
use OCA\PhotoFrame\Db\FrameMapper; ?>

<div class="app-content">
  <h2>Photo Frames</h2>

  <div class="list">
    <?php foreach ($_['frames'] as $frame): ?>
      <div class="frame">
        <img src="/index.php/apps/photoframe/<?= $frame->getShareToken() ?>/image" />
        <div class="grow">
          <div class="flex">
            <h2 class="grow">
              <?= $frame->getName() ?>
            </h2>

            <form data-delete data-confirm="Are you sure that you want to delete the frame"
              action="/index.php/apps/photoframe/<?= $frame->getId() ?>">
              <button class="error">Delete</button>
            </form>
          </div>
          <p>
            <strong>Album:</strong> <?= $frame->getAlbumName() ?>
          </p>

          <p>
            <strong>Select:</strong>
            <?= [
              FrameMapper::SELECTION_METHOD_LATEST => "Latest",
              FrameMapper::SELECTION_METHOD_OLDEST => "Oldest",
              FrameMapper::SELECTION_METHOD_RANDOM => "Random"
            ][$frame->getSelectionMethod()];
            ?>
          </p>
          <p>
            <strong>Rotation:</strong>
            <?= [
              FrameMapper::ENTRY_LIFETIME_ONE_HOUR => "1 per hour",
              FrameMapper::ENTRY_LIFETIME_1_4_DAY => "4 per day",
              FrameMapper::ENTRY_LIFETIME_1_3_DAY => "3 per day",
              FrameMapper::ENTRY_LIFETIME_1_2_DAY => "2 per day",
              FrameMapper::ENTRY_LIFETIME_ONE_DAY => "1 per day"
            ][$frame->getEntryLifetime()];
            ?>
          </p>
          <p>
            <strong>Start day at:</strong>
            <?= $frame->getStartDayAt() ?>
          </p>
          <p>
            <strong>End day at:</strong>
            <?= $frame->getEndDayAt() ?>
          </p>
          <div class="actions">
            <a target="_BLANK" href="/index.php/apps/photoframe/<?= $frame->getShareToken() ?>">
              <button>Show</button>
            </a>
            <a href="/index.php/apps/photoframe/<?= $frame->getId() ?>/edit">
              <button>Edit</button>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <a href="/index.php/apps/photoframe/new">
    <button class="primary">New frame</button>
  </a>
</div>

<script type="text/javascript" nonce="<?= $_['cspNonce']; ?>">
  [...document.querySelectorAll('form[data-confirm]')].forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
        return;
      }

      if (form.hasAttribute('data-delete')) {
        event.preventDefault();
        const response = await fetch(form.action, { method: 'DELETE' })
        if (response.ok) form.closest('tr').remove();
      }
    })
  })
</script>
