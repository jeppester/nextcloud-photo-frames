<?php declare(strict_types=1);
use OCA\PhotoFrames\Db\FrameMapper; ?>

<div class="app-content">
  <h2>Photo Frames</h2>

  <?php if (!$isTestedPhotosVersion): ?>
    <p class="alert alert--info">
      You are using an untested version of photos. Tested version are 3, 4, and 5<br />
    </p>
  <?php endif; ?>

  <div class="list">
    <?php foreach ($_['frames'] as $frame): ?>
      <div class="frame">
        <img
          src="<?= $urlGenerator->linkToRouteAbsolute('photo_frames.page.photoframeImage', ["shareToken" => $frame->getShareToken()]) ?>" />
        <div class="grow">
          <div class="flex">
            <h2 class="grow">
              <?= $frame->getName() ?>
            </h2>

            <form data-delete data-confirm="Are you sure that you want to delete the frame"
              action="<?= $urlGenerator->linkToRoute('photo_frames.page.destroy', ["id" => $frame->getId()]) ?>">
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
          <p>
            <strong>Show date:</strong>
            <?= $frame->getShowPhotoTimestamp() ? "Enabled" : "Disabled" ?>
          </p>
          <div class="actions">
            <a target="_BLANK"
              href="<?= $urlGenerator->linkToRoute('photo_frames.page.photoframe', ["shareToken" => $frame->getShareToken()]) ?>">
              <button>Show</button>
            </a>
            <a href="<?= $urlGenerator->linkToRoute('photo_frames.page.edit', ["id" => $frame->getId()]) ?>">
              <button>Edit</button>
            </a>
            <button
              data-qr-link="<?= $urlGenerator->linkToRouteAbsolute('photo_frames.page.photoframe', ["shareToken" => $frame->getShareToken()]) ?>">
              Show QR
            </button>
            <button class="primary"
              data-copy-link="<?= $urlGenerator->linkToRouteAbsolute('photo_frames.page.photoframe', ["shareToken" => $frame->getShareToken()]) ?>">
              Copy link
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="flex actions">
    <div class="grow"></div>
    <a href="<?= $urlGenerator->linkToRoute('photo_frames.page.new') ?>">
      <button class="primary">New frame</button>
    </a>
  </div>

  <div class="modal-backdrop">
    <div class="modal">
      <div class="modal-content"></div>
      <button class="modal-close primary">
        Close
      </button>
    </div>
  </div>
</div>

<script type="text/javascript" nonce="<?= $_['cspNonce']; ?>">
  [...document.querySelectorAll('button[data-copy-link]')].forEach((button) => {
    button.addEventListener('click', async () => {
      const prevContent = button.innerHTML
      button.disabled = true
      button.innerHTML = "Copied"

      try {
        await navigator.clipboard.writeText(button.getAttribute('data-copy-link'))
      }
      finally {
        setTimeout(() => {
          button.disabled = false
          button.innerHTML = prevContent
        }, 1000)
      }
    })
  });

  [...document.querySelectorAll('form[data-confirm]')].forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
        return;
      }

      if (form.hasAttribute('data-delete')) {
        event.preventDefault();
        const response = await fetch(form.action, { method: 'DELETE' })
        if (response.ok) form.closest('.frame').remove();
      }
    })
  });

  [...document.querySelectorAll('button[data-qr-link]')].forEach((button) => {
    button.addEventListener('click', async (event) => {
      const link = button.getAttribute('data-qr-link')
      const modalContent = document.querySelector('.modal-content')
      modalContent.innerHTML = ''

      const div = document.createElement('div')
      div.style.border = "10px solid white";
      modalContent.append(div)

      document.querySelector('.modal-backdrop').classList.add('modal-backdrop--visible')

      new QRCode(div, link);
    })
  });

  document.querySelector('.modal-close').addEventListener('click', () => {
    document.querySelector('.modal-backdrop').classList.remove('modal-backdrop--visible')
  })
  document.querySelector('.modal-backdrop').addEventListener('click', (event) => {
    if (event.target === event.currentTarget) {
      event.currentTarget.classList.remove('modal-backdrop--visible')
    }
  })
</script>