<?php declare(strict_types=1); ?>

<div class="app-content">
</div>

<script nonce="<?= $_['cspNonce']; ?>">
  window.appPath = "<?= $_['appPath'] ?>"
</script>
<script type="module" nonce="<?= $_['cspNonce']; ?>">
  import { html, render } from "<?= $_['appPath'] ?>/js/vendor/htm-preact-standalone.min.mjs";

  const pageProps = <?= json_encode($_['pageProps'], JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;

  import Page from "<?= $_['appPath'] ?>/js/pages/<?= $_['pageName'] ?>.mjs"
  render(html`<${Page} ...${pageProps} />`, document.querySelector('.app-content'));
</script>