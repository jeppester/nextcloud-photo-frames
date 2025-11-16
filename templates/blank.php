<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="<?= $_['appPath'] ?>/css/fonts.css" />
  <style>
    :root {
      background-color:
        <?= $_['backgroundColor'] ?>
      ;
    }
  </style>

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
  <?php emit_css_loading_tags($_); ?>

  <?php if (isset($_['javascript'])): ?>
    <script nonce="<?= $_['cspNonce']; ?>">
      window.addEventListener('DOMContentLoaded', () => {
        <?= $_['javascript']; ?>
      })
    </script>
  <?php endif ?>
</head>

<body></body>

</html>