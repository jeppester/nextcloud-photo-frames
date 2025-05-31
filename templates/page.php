<?php declare(strict_types=1); ?>

<div class="app-content">
</div>

<script type="module" nonce="<?= $_['cspNonce']; ?>">
  import { html, render } from "<?= $appPath ?>/js/vendor/htm-preact-standalone.min.mjs";

  const pageProps = <?= json_encode($pageProps, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;

  import Page from "<?= $appPath ?>/js/pages/<?= $pageName ?>.mjs"
  render(html`<${Page} ...${pageProps} />`, document.querySelector('.app-content'));
</script>