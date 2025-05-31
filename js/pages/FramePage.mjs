import { html, useState } from "../vendor/htm-preact-standalone.min.mjs";
import { css, keyframes } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

const rotationUnitRefreshInterval = {
  day: 1000 * 60, // One minute
  hour: 1000 * 60, // One minute
  minute: 1000, // One second
};

const animations = {
  fadeIn: keyframes`
    from { opacity: 0; }
    to { opacity: 100; }
  `,
};

const styles = {
  photoFrame: css`
    animation: fade-in 2s ease-in-out;
    background-color: #222;
    position: absolute;
    width: 100vw;
    height: 100vh;
    background-position: center center;
    background-repeat: no-repeat;
    background-size: contain;
  `,
  date: css`
    text-transform: uppercase;
    font-family: "Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS",
      sans-serif;
    position: fixed;
    margin: 0;
    bottom: 0;
    left: 0;
    padding: 0.5rem 1rem 0.3rem;
    background-color: #333;
    font-size: 1.5rem;
    font-weight: normal;
    color: #bba;
    border: 1px solid rgba(255, 255, 255, 0.05);
    outline: 1px solid #333;
    text-shadow: 0px 1px 0px black;
    box-shadow: 0px 5px 40px rgba(0, 0, 0, 0.2);
  `,
};

export default function FramePage(props) {
  const { shareToken, frameFile, showPhotoTimestamp, rotationUnit } = props;
  const [images, setImages] = useState([
    {
      url: generateUrl("apps/photo_frames/{shareToken}/image", {
        shareToken: shareToken,
      }),
      timestamp: new Date(frameFile.capturedAtTimestamp * 1000),
    },
  ]);

  return html`
    ${images.map(
      (image) => html`
        <div
          className=${styles.photoFrame}
          style=${{ backgroundImage: `url('${image.url}')` }}
        >
          ${showPhotoTimestamp &&
          html`
            <h1 className=${styles.date}>
              ${Intl.DateTimeFormat(navigator.locale, {
                month: "long",
                year: "numeric",
              }).format(image.timestamp)}
            </h1>
          `}
        </div>
      `
    )}
  `;
}

//     let expiry = new Date("<?= $frameFile->getExpiresHeader(); ?>")

//     // Set refreshInterval based on the rotation unit
//     let refreshInterval = rotationUnitRefreshInterval["<?= $rotationUnit ?>"]

//     const imageUrl = `${location.href}/image`

//     async function updateImage() {
//       const now = new Date();

//       // Always set new timeout so that the frame is resilient to network errors from here and down
//       setTimeout(updateImage, refreshInterval)
//       if (now < expiry) return

//       const headResponse = await fetch(imageUrl, { method: "HEAD", cache: "reload" })
//       const nextExpiresAt = new Date(headResponse.headers.get('expires'))

//       const isNewImage = nextExpiresAt > expiry
//       if (!isNewImage) return;

//       const imageResponse = await fetch(imageUrl)
//       const blob = await imageResponse.blob()
//       // Read the Blob as DataURL using the FileReader API
//       const reader = new FileReader();
//       reader.onloadend = () => {
//         const frame = document.querySelector('.photoFrame')
//         const newFrame = document.createElement('div')
//         newFrame.classList.add('photoFrame')
//         newFrame.style.backgroundImage = `url('${reader.result}')`;
//         document.body.appendChild(newFrame)

//         // Now that the new image is loaded, update expiry and refresh interval
//         expiry = new Date(imageResponse.headers.get('expires'))
//         const rotationUnit = imageResponse.headers.get('X-Frame-Rotation-Unit')
//         refreshInterval = rotationUnitRefreshInterval[rotationUnit]
//         const timestampElement = document.createElement('h1')
//         const timestamp = new Date(imageResponse.headers.get('X-Photo-Timestamp') * 1000)
//         const formattedTimestamp = Intl.DateTimeFormat(navigator.locale, { month: 'long', year: "numeric" }).format(timestamp)
//         timestampElement.innerHTML = formattedTimestamp
//         newFrame.append(timestampElement)

//         // We cannot rely on the animation as it might not happen when the window is not focused
//         setTimeout(() => {
//           frame.remove()
//         }, 2000);
//       };
//       reader.readAsDataURL(blob);
//     }

//     setTimeout(updateImage, refreshInterval)
//   </script>
// </head>

// </html>
