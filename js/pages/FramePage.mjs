import {
  html,
  useEffect,
  useRef,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";
import { injectGlobal } from "../vendor/emotion-css.min.mjs";
import Frame from "../components/Frame.mjs";
import EmptyAlbum from "../components/EmptyAlbum.mjs";
import PhotoCouldNotBeLoaded from "../components/PhotoCouldNotBeLoaded.mjs";
import dispatch from "../utils/dispatch.mjs";
import timedFetch from "../utils/timedFetch.mjs";

const rotationUnitRefreshInterval = {
  day: 1000 * 60, // One minute
  hour: 1000 * 60, // One minute
  minute: 1000, // One second
};

injectGlobal`
  :root {
    font-size: 16px;
  }
  body {
    margin: 0;
  }
`;

async function responseToBlobUrl(imageResponse) {
  // Read the image to a DataURL using the FileReader API
  // This is to prevent the browser from caching the image URL (which is the same for all images)
  const blob = await imageResponse.blob();

  const reader = new FileReader();
  dispatch("pf:image-loadstart", { reader });

  const result = await new Promise((res, rej) => {
    reader.onloadend = () => res(reader.result);
    reader.onerror = () => rej();
    reader.readAsDataURL(blob);
  });

  dispatch("pf:image-loadend", { reader });
  return result;
}

let nextTimeoutAt = Date.now();
const getNextTimeoutDelay = (currentImage) => {
  if (!currentImage) return 0;

  while (nextTimeoutAt <= Date.now()) {
    nextTimeoutAt += currentImage.refreshInterval;
  }

  return nextTimeoutAt - Date.now();
};

class AlbumEmptyError extends Error {}
class UpdateImageError extends Error {}

export default function FramePage(props) {
  const {
    showPhotoTimestamp,
    showPhotoPlace,
    showClock,
    photoSize,
    backgroundType,
    backgroundColor,
  } = props;
  const imageUrl = location.href + "/image";
  const [images, setImages] = useState([]);
  const [albumIsEmpty, setAlbumIsEmpty] = useState(false);
  const [error, setError] = useState(false);
  const imageRef = useRef(null);

  useEffect(() => {
    let loopTimeout;

    const updateImage = async () => {
      if (imageRef.current) {
        // Fetch the newest image's expiry, continue if the expiry is
        // not the same as the current image (e.g. it really IS a new image)
        const headResponse = await timedFetch(10000, imageUrl, {
          method: "HEAD",
          cache: "reload",
        });
        const nextExpiresAt = new Date(headResponse.headers.get("expires"));
        if (imageRef.current.expiresAt >= nextExpiresAt) return;
      }

      const imageResponse = await timedFetch(10000, imageUrl);

      // If image returns a 404, it means the albums has no images
      if (imageResponse.status === 404) throw new AlbumEmptyError();

      // Only continue if the response is ok
      if (!imageResponse.ok) throw new UpdateImageError();

      const blobUrl = await responseToBlobUrl(imageResponse);

      // Prepare next image
      const rotationUnit = imageResponse.headers.get("X-Frame-Rotation-Unit");
      const nextImage = {
        url: blobUrl,
        expiresAt: new Date(imageResponse.headers.get("expires")),
        place: decodeURIComponent(imageResponse.headers.get("X-Photo-Place")),
        timestamp: new Date(
          imageResponse.headers.get("X-Photo-Timestamp") * 1000
        ),
        refreshInterval: rotationUnitRefreshInterval[rotationUnit],
      };
      imageRef.current = nextImage;

      setImages((images) => [images.at(0), nextImage].filter(Boolean));

      // Remove previous image when the new image has faded in
      if (imageRef.current) {
        dispatch("pf:image-fadestart", { image: nextImage });
        await new Promise((resolve) => {
          setTimeout(() => {
            setImages([nextImage]);
            dispatch("pf:image-visible", { image: nextImage });
            resolve();
          }, 2000);
        });
      } else {
        dispatch("pf:image-visible", { image: nextImage });
        dispatch("pf:frame-ready", { image: nextImage });
      }
    };

    const loop = async function () {
      try {
        // Update image if expired
        if (Date.now() > imageRef.current.expiresAt) await updateImage();
      } finally {
        // Always schedule new loop
        loopTimeout = setTimeout(loop, getNextTimeoutDelay(imageRef.current));
      }
    };

    // Do initial image update
    updateImage()
      .then(() => {
        // If successful, start update loop
        loopTimeout = setTimeout(loop, getNextTimeoutDelay());
      })
      .catch((e) => {
        // If not successful, show empty or error state
        if (e instanceof AlbumEmptyError) {
          setAlbumIsEmpty(true);
          dispatch("pf:frame-ready", { image: null });
        }
        setError(true);
      });

    return () => clearTimeout(loopTimeout);
  }, []);

  if (albumIsEmpty) {
    return html` <${EmptyAlbum} /> `;
  }

  if (error) {
    return html` <${PhotoCouldNotBeLoaded} /> `;
  }

  return html`
    ${images.map(
      (image) =>
        html`<${Frame}
          key=${image.expiresAt}
          showPhotoTimestamp=${showPhotoTimestamp}
          showPhotoPlace=${showPhotoPlace}
          showClock=${showClock}
          photoSize=${photoSize}
          backgroundType=${backgroundType}
          backgroundColor=${backgroundColor}
          image=${image}
        />`
    )}
  `;
}
