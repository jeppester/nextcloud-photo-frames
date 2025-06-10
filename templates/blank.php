<!DOCTYPE html>
<html>

<head>
  <script nonce="<?= $_['cspNonce']; ?>">
    window.appPath = "<?= $_['appPath'] ?>"
  </script>
  <script type="module" nonce="<?= $_['cspNonce']; ?>">
    import { html, render } from "<?= $_['appPath']; ?>/js/vendor/htm-preact-standalone.min.mjs";
    import Page from "<?= $_['appPath'] ?>/js/pages/<?= $_['pageName'] ?>.mjs"

    const pageProps = <?= json_encode($_['pageProps'], JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
    document.addEventListener('DOMContentLoaded', () => {
      render(html`<${Page} ...${pageProps} />`, document.querySelector('body'));
    })
  </script>
</head>

<body></body>

</html>