import {
  html,
  useCallback,
  useEffect,
  useRef,
} from "../vendor/htm-preact-standalone.min.mjs";
import { css, keyframes } from "../vendor/emotion-css.min.mjs";
import getLoadedImage from "../utils/getLoadedImage.mjs";
import renderSmartFittedImage from "../utils/renderSmartFittedImage.mjs";
import ImageDetails from "./ImageDetails.mjs";
import Clock from "./Clock.mjs";

const animations = {
  fadeIn: keyframes`
    from { opacity: 0; }
    to { opacity: 100; }
  `,
};

const styles = {
  frame: css`
    background-color: #000;
    position: absolute;
    width: 100%;
    height: 100%;
    font-family: "Noto Sans", sans-serif;

    // Only animate when adding a frame on top of another frame
    & + & {
      animation: ${animations.fadeIn} 2s ease-in-out;
    }
  `,
  photoBackground: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: 100% 100%;
    filter: blur(6.25em) brightness(70%);
  `,
  colorBackground: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  `,
  photo: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-repeat: no-repeat;

    &.stretch {
      background-size: 100% 100%;
    }

    &.contain,
    &.smart-fit {
      background-size: contain;
    }

    &.cover {
      background-size: cover;
    }
  `,
  clockContainer: css`
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: end;
    padding: 1.2em;
  `,
  detailsContainer: css`
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: start;
    padding: 1em 1.2em;
  `,
};

export default function Frame(props) {
  const {
    showPhotoTimestamp,
    showPhotoPlace,
    showClock,
    photoSize,
    image,
    backgroundType,
    backgroundColor,
  } = props;
  const canvasRef = useRef();
  const loadedImageRef = useRef();
  const showBackground = ["contain", "smart-fit"].includes(photoSize);
  const renderOnCanvas = ["smart-fit"].includes(photoSize);

  const renderImage = useCallback(async () => {
    renderSmartFittedImage(loadedImageRef.current, canvasRef.current);
  }, []);

  useEffect(() => {
    if (!renderOnCanvas) return;

    (async () => {
      if (!loadedImageRef.current) {
        loadedImageRef.current = await getLoadedImage(image.url);
      }
      renderImage();
    })();

    window.addEventListener("resize", renderImage);
    return () => window.removeEventListener("resize", renderImage);
  }, [renderOnCanvas]);

  return html`
    <div className=${styles.frame}>
      ${showBackground &&
      html`
        ${backgroundType === "aura"
          ? html`
              <div
                className=${styles.photoBackground}
                style=${{ backgroundImage: `url("${image.url}")` }}
              />
            `
          : html`
              <div
                className=${styles.colorBackground}
                style=${{ backgroundColor: backgroundColor }}
              />
            `}
      `}
      ${renderOnCanvas
        ? html`<canvas ref=${canvasRef} className=${styles.photo} />`
        : html`
            <div
              className=${[styles.photo, photoSize].join(" ")}
              style=${{ backgroundImage: `url("${image.url}")` }}
            />
          `}
      ${showClock &&
      html`
        <div className=${styles.clockContainer}>
          <${Clock} image=${image} />
        </div>
      `}
      <div className=${styles.detailsContainer}>
        <${ImageDetails}
          showTimestamp=${showPhotoTimestamp}
          showPlace=${showPhotoPlace}
          image=${image}
        />
      </div>
    </div>
  `;
}
