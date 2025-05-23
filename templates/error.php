<?php declare(strict_types=1); ?>

<div class="app-content">
  <h2>Photo Frames</h2>
  <p class="alert alert--danger">
    <?= $message ?>
  </p>

  <?php if (isset($issueTitle)): ?>
    <p>
      If you keep seeing this error, please report it
      <strong>
        <u>
          <a target="_BLANK"
            href="https://github.com/jeppester/nextcloud-photo-frames/issues/new?title=<?= urlencode($issueTitle) ?>&body=<?= urlencode($issueBody) ?>">
            here
          </a>
        </u>
      </strong>
    </p>
  <?php endif; ?>
</div>