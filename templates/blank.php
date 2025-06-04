<!DOCTYPE html>
<html>

<head>
  <script nonce="<?= $_['cspNonce']; ?>">
    window.appPath = "<?= $appPath ?>"
  </script>
  <script type="module" nonce="<?= $_['cspNonce']; ?>">
    import { html, render } from "<?= $appPath ?>/js/vendor/htm-preact-standalone.min.mjs";
    import Page from "<?= $appPath ?>/js/pages/<?= $pageName ?>.mjs"

    const pageProps = <?= json_encode($pageProps, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
    document.addEventListener('DOMContentLoaded', () => {
      render(html`<${Page} ...${pageProps} />`, document.querySelector('body'));
    })
  </script>
</head>

<body></body>

</html>