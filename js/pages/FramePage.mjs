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
  :root {
    background-color: #1c1c1c;
    font-size: 16px;
  }
  body {
    margin: 0;
  }
`;

const styles = {
  photoFrame: css`
    animation: ${animations.fadeIn} 2s ease-in-out;
    background-color: #1c1c1c;
    position: absolute;
    width: 100vw;
    height: 100vh;
    background-position: center center;
    background-repeat: no-repeat;
    background-size: contain;
  `,
  dateContainer: css`
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    padding: 0.3rem;
  `,
  date: css`
    text-transform: capitalize;
    font-family: Georgia, "Times New Roman", Times, serif;
    font-weight: 400;
    border-radius: 0.2rem;
    margin: 0;
    padding: 0.2rem 0.65rem 0.2rem;
    background-color: rgba(0, 0, 0, 0.6);
    font-size: 1rem;
    color: rgb(200, 194, 189);
    border: 1px solid rgba(213, 204, 195, 0.3);
    outline: 1px solid rgba(0, 0, 0, 0.6);
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

        // Remove previous image when the new image has faded in
        if (currentImage) setTimeout(() => setImages([nextImage]), 2000);
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
            <div className=${styles.dateContainer}>
              <h1 className=${styles.date}>
                ${Intl.DateTimeFormat(navigator.locale, {
                  month: "long",
                  year: "numeric",
                }).format(image.timestamp)}
              </h1>
            </div>
          `}
        </div>
      `
    )}
  `;
}
