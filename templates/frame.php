<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html>

<head>
  <style>
    :root {
      background-color: <?= $styleBackgroundColor ?>;
    }

    :root,
    body {
      margin: 0;
      font-size: 16px;
    }

    @keyframes fade-in {
      from {
        opacity: 0;
      }

      to {
        opacity: 100;
      }
    }

    .photoFrame {
      animation: fade-in 2s ease-in-out;
      background-color: <?= $styleBackgroundColor ?>;
      position: absolute;
      width: 100vw;
      height: 100vh;
      background-position: center center;
      background-repeat: no-repeat;
      background-size: <?= $styleFill ? 'cover' : 'contain' ?>;
    }

    .photoFrame h1 {
      display:
        <?= $showPhotoTimestamp ? "block" : "none" ?>
      ;
      text-transform: uppercase;
      font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
      position: fixed;
      margin: 0;
      bottom: 0;
      left: 0;
      padding: .5rem 1rem .3rem;
      background-color: <?= $styleBackgroundColor ?>;
      font-size: 1.5rem;
      font-weight: normal;
      color: #bba;
      border: 1px solid rgba(255, 255, 255, 0.05);
      outline: 1px solid #333;
      text-shadow: 0px 1px 0px black;
      box-shadow: 0px 5px 40px rgba(0, 0, 0, .2)
    }
  </style>

  <script type="text/javascript" defer nonce="<?php echo $_['cspNonce']; ?>">
    let expiry = new Date("<?= $frameFile->getExpiresHeader(); ?>")

    // Set refreshInterval based on the rotation unit
    const rotationUnitRefreshInterval = {
      day: 1000 * 60, // One minute
      hour: 1000 * 60, // One minute
      minute: 1000, // One second
    }
    let refreshInterval = rotationUnitRefreshInterval["<?= $rotationUnit ?>"]

    const imageUrl = `${location.href}/image`

    async function updateImage() {
      const now = new Date();

      // Always set new timeout so that the frame is resilient to network errors from here and down
      setTimeout(updateImage, refreshInterval)
      if (now < expiry) return

      const headResponse = await fetch(imageUrl, { method: "HEAD", cache: "reload" })
      const nextExpiresAt = new Date(headResponse.headers.get('expires'))

      const isNewImage = nextExpiresAt > expiry
      if (!isNewImage) return;

      const imageResponse = await fetch(imageUrl)
      const blob = await imageResponse.blob()
      // Read the Blob as DataURL using the FileReader API
      const reader = new FileReader();
      reader.onloadend = () => {
        const frame = document.querySelector('.photoFrame')
        const newFrame = document.createElement('div')
        newFrame.classList.add('photoFrame')
        newFrame.style.backgroundImage = `url('${reader.result}')`;
        document.body.appendChild(newFrame)

        // Now that the new image is loaded, update expiry and refresh interval
        expiry = new Date(imageResponse.headers.get('expires'))
        const rotationUnit = imageResponse.headers.get('X-Frame-Rotation-Unit')
        refreshInterval = rotationUnitRefreshInterval[rotationUnit]
        const timestampElement = document.createElement('h1')
        const timestamp = new Date(imageResponse.headers.get('X-Photo-Timestamp') * 1000)
        const formattedTimestamp = Intl.DateTimeFormat(navigator.locale, { month: 'long', year: "numeric" }).format(timestamp)
        timestampElement.innerHTML = formattedTimestamp
        newFrame.append(timestampElement)

        // We cannot rely on the animation as it might not happen when the window is not focused
        setTimeout(() => {
          frame.remove()
        }, 2000);
      };
      reader.readAsDataURL(blob);
    }

    setTimeout(updateImage, refreshInterval)
  </script>
</head>

<div class="photoFrame"
  style="background-image: url('<?= $urlGenerator->linkToRouteAbsolute('photo_frames.page.photoframe', ["shareToken" => $shareToken]) ?>/image')">
  <h1>
    <script type="text/javascript" nonce="<?php echo $_['cspNonce']; ?>">
      {
        const timestamp = new Date(<?php echo $frameFile->getCapturedAtTimestamp(); ?>000);
        document.write(Intl.DateTimeFormat(navigator.locale, { month: 'long', year: "numeric" }).format(timestamp))
      }
    </script>
  </h1>
</div>

</html>