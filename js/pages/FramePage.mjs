import {
  html,
  useEffect,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";
import { css, keyframes, injectGlobal } from "../vendor/emotion-css.min.mjs";
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

injectGlobal`
  :root { background-color: #222; }
  :root,
  body {
    margin: 0;
    font-size: 16px;
  }
`;

const styles = {
  photoFrame: css`
    animation: ${animations.fadeIn} 2s ease-in-out;
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
  const { shareToken, showPhotoTimestamp } = props;
  const imageUrl = generateUrl("apps/photo_frames/{shareToken}/image", {
    shareToken: shareToken,
  });
  const [images, setImages] = useState([]);

  const currentImage = images.at(-1);
  useEffect(() => {
    let timeout;

    const updateImage = async () => {
      const now = new Date();

      // If we already have an image, set a timeout up front,
      // and return early unless a new image is available
      if (currentImage) {
        timeout = setTimeout(updateImage, currentImage.refreshInterval);

        // Return early unless we've exceeded the image's expiry
        if (currentImage.expiresAt > now) return;

        // Fetch the newest image's expiry, continue if the expiry is
        // not the same as the current image (e.g. it really IS a new image)
        const headResponse = await fetch(imageUrl, {
          method: "HEAD",
          cache: "reload",
        });
        const nextExpiresAt = new Date(headResponse.headers.get("expires"));
        if (currentImage.expiresAt >= nextExpiresAt) return;
      }

      // Read the image to a DataURL using the FileReader API
      // This is to prevent the browser from caching the image URL (which is the same for all images)
      const imageResponse = await fetch(imageUrl);
      const blob = await imageResponse.blob();
      const reader = new FileReader();
      reader.onloadend = () => {
        const rotationUnit = imageResponse.headers.get("X-Frame-Rotation-Unit");
        const nextImage = {
          url: reader.result,
          expiresAt: new Date(imageResponse.headers.get("expires")),
          timestamp: new Date(
            imageResponse.headers.get("X-Photo-Timestamp") * 1000
          ),
          refreshInterval: rotationUnitRefreshInterval[rotationUnit],
        };
        setImages([currentImage, nextImage].filter(Boolean));

        // We cannot use animationEnd as it only reliably triggers when the window has focus
        setTimeout(() => setImages([nextImage]), 2000);
      };
      reader.readAsDataURL(blob);
    };

    timeout = setTimeout(updateImage, currentImage?.refreshInterval || 0);
    return () => clearTimeout(timeout);
  }, [currentImage]);

  return html`
    ${images.map(
      (image) => html`
        <div
          className=${styles.photoFrame}
          key=${image.expiresAt}
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
