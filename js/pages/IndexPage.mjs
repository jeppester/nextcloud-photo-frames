import {
  html,
  useRef,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

import CopyButton from "../components/CopyButton.mjs";
import Actions from "../components/Actions.mjs";

const styles = {
  list: css`
    width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
    margin-bottom: 1rem;

    @media (max-width: calc(550px + 3rem)) {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      margin-top: 1rem;
      margin-bottom: 1rem;
    }
  `,
  frame: css`
    border: 2px solid #00679e88;
    background-color: #00679e09;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    gap: 2rem;
    overflow: hidden;

    @media (max-width: calc(550px + 3rem)) {
      flex-direction: column;
    }

    img {
      width: 250px;
      object-fit: cover;
      margin: -1.5rem;
      margin-right: 0;

      @media (max-width: calc(550px + 3rem)) {
        width: calc(100% + 3rem);
        margin-right: -1.5rem;
        margin-bottom: 0;
        aspect-ratio: 16/9;
      }
    }

    h2 {
      font-size: 1.5rem;
      font-weight: 500;
      margin: 0 0 0.5rem;
    }

    p {
      font-size: 16px;
      font-weight: 500;
      margin: 0;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      margin-top: 1rem;
      gap: 0.5rem;
    }
  `,
  modal: css`
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.4);

    &.visible {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .container {
      background-color: var(--color-main-background);
      padding: 1rem;
      display: flex;
      gap: 1rem;
      flex-direction: column;
      align-items: center;
      border-radius: 0.5rem;
      box-shadow: 0px 10px 50px rgba(0, 0, 0, 0.4);
    }
  `,
};

const urlForFrame = ({ shareToken }) =>
  location.origin +
  generateUrl("apps/photo_frames/{shareToken}", { shareToken });

export default function IndexPage(props) {
  const [modalShown, setModalShown] = useState(false);
  const modalRef = useRef();

  // Store frames in state so that we can update the UI when a frame is deleted
  const [frames, setFrames] = useState(props.frames);

  const showQRCode = (frame) => {
    const modalContent = modalRef.current.querySelector(".content");
    modalContent.innerHTML = "";

    const div = document.createElement("div");
    div.style.border = "10px solid white";
    modalContent.append(div);

    setModalShown(true);
    new QRCode(div, urlForFrame(frame));
  };

  const closeModal = () => setModalShown(false);

  const deleteFrame = async (frame) => {
    if (!confirm("Are you sure that you want to delete the frame?")) return;

    const prevFrames = frames.slice();
    setFrames(prevFrames.filter((f) => f.id !== frame.id));

    const deleteUrl = generateUrl("apps/photo_frames/{id}", { id: frame.id });
    const response = await fetch(deleteUrl, { method: "DELETE" });

    if (!response.ok) setFrames(prevFrames);
  };

  return html`
    <div className="flex">
      <h2>Photo Frames</h2>
      <a href=${generateUrl("apps/photo_frames/new")}>
        <button className="primary">New frame</button>
      </a>
    </div>

    <div className=${styles.list}>
      ${frames.map((frame) => {
        return html`
          <div className=${styles.frame}>
            <img
              src=${generateUrl("apps/photo_frames/{shareToken}/image", {
                shareToken: frame.shareToken,
              })}
            />
            <div className="grow">
              <div className="flex">
                <h2 className="grow">${frame.name}</h2>

                <button className="error" onClick=${() => deleteFrame(frame)}>
                  Delete
                </button>
              </div>
              <p><strong>Album:</strong> ${frame.albumName}</p>

              <p>
                <strong>Select:</strong>
                ${" "}
                ${{
                  latest: "Latest",
                  oldest: "Oldest",
                  random: "Random",
                }[frame.selectionMethod]}
              </p>
              <p>
                <strong>Rotation: </strong>
                ${frame.rotationsPerUnit}
                ${frame.rotationsPerUnit === 1 ? " photo " : " photos "} per
                ${" "}
                ${{
                  day: "day",
                  hour: "hour",
                  minute: "minute",
                }[frame.rotationUnit]}
              </p>
              <p>
                <strong>Start day at:</strong>
                ${" "}${frame.startDayAt}
              </p>
              <p>
                <strong>End day at:</strong>
                ${" "}${frame.endDayAt}
              </p>
              <p>
                <strong>Show date:</strong>
                ${" "}${frame.showPhotoTimestamp ? "Enabled" : "Disabled"}
              </p>
              <${Actions}>
                <a
                  target="_BLANK"
                  href=${generateUrl("apps/photo_frames/{shareToken}", {
                    shareToken: frame.shareToken,
                  })}
                >
                  <button>Show</button>
                </a>
                <a
                  href=${generateUrl("apps/photo_frames/{id}/edit", {
                    id: frame.id,
                  })}
                >
                  <button>Edit</button>
                </a>
                <button onClick=${() => showQRCode(frame)}>Show QR</button>
                <${CopyButton}
                  className="primary"
                  data=${urlForFrame(frame)}
                  copiedText="Copied"
                >
                  Copy link
                <//>
              <//>
            </div>
          </div>
        `;
      })}
    </div>

    <div
      ref=${modalRef}
      className=${`${styles.modal} ${modalShown ? "visible" : ""}`}
      onClick=${(e) => e.target === e.currentTarget && closeModal()}
    >
      <div className="container">
        <div className="content"></div>
        <button className="primary" onClick=${closeModal}>Close</button>
      </div>
    </div>
  `;
}
