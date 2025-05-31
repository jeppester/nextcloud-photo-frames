<!DOCTYPE html>
<html>

<head>
  <style>
    :root {
      background-color: #222;
    }

    :root,
    body {
      margin: 0;
      font-size: 16px;
    }
  </style>

  <script type="module" nonce="<?= $_['cspNonce']; ?>">
    import { html, render } from "<?= $appPath ?>/js/vendor/htm-preact-standalone.min.mjs";

    const pageProps = <?= json_encode($pageProps, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;

    import Page from "<?= $appPath ?>/js/pages/<?= $pageName ?>.mjs"

    document.addEventListener('DOMContentLoaded', () => {
      render(html`<${Page} ...${pageProps} />`, document.querySelector('body'));
    })
  </script>
</head>

<body>
</body>

</html>