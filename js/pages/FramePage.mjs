import {
  html,
  useEffect,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";
import { css, keyframes, injectGlobal } from "../vendor/emotion-css.min.mjs";

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
  frame: css`
    animation: ${animations.fadeIn} 2s ease-in-out;
    background-color: #000;
    position: absolute;
    width: 100vw;
    height: 100vh;
  `,
  photoBackground: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: 100% 100%;
    filter: blur(100px) brightness(70%);
  `,
  photo: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
  `,
  dateContainer: css`
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: start;
    padding: 1rem;
  `,
  dateBackground: css`
    border-radius: 1rem;
    border-top-left-radius: 0;
    border-bottom-right-radius: 0;
    padding: 1rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: start;
    background-color: rgba(255, 250, 250, 0.4);
    font-size: 2rem;
    border: 1px solid rgba(213, 204, 195, 0.3);
    box-shadow: 0px 5px 40px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(5px);
  `,
  date: css`
    text-align: left;
    text-transform: capitalize;
    font-family: sans-serif;
    margin: 0;
    color: rgb(35, 18, 5);
    text-shadow: 0px 0px 1px rgba(255, 255, 255, 0.4);
  `,
  dateSpacer: css`
    height: 1.5px;
    margin: 0.25rem 1rem 0 0.1rem;
    background-color: rgb(35, 18, 5);
  `,
  year: css`
    font-size: 2rem;
    font-weight: 600;
  `,
  month: css`
    font-size: 1.2rem;
    font-weight: 500;
    margin-left: 0.05rem;
    padding-bottom: 0.2rem;
    border-bottom: 1.5px solid rgb(35, 18, 5);
  `,
};

export default function FramePage(props) {
  const { showPhotoTimestamp } = props;
  const imageUrl = location.href + "/image";
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
        <div className=${styles.frame} key=${image.expiresAt}>
          <div
            className=${styles.photoBackground}
            style=${{ backgroundImage: `url("${image.url}")` }}
          />
          <div
            className=${styles.photo}
            style=${{ backgroundImage: `url("${image.url}")` }}
          />

          ${showPhotoTimestamp &&
          html`
            <div className=${styles.dateContainer}>
              <div className=${styles.dateBackground}>
                <h1 className=${`${styles.date} ${styles.month}`}>
                  ${Intl.DateTimeFormat(navigator.locale, {
                    month: "short",
                  }).format(image.timestamp)}
                </h1>
                <h1 className=${`${styles.date} ${styles.year}`}>
                  ${Intl.DateTimeFormat(navigator.locale, {
                    year: "numeric",
                  }).format(image.timestamp)}
                </h1>
              </div>
            </div>
          `}
        </div>
      `
    )}
  `;
}
