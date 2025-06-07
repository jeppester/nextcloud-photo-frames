import {
  html,
  useRef,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

import FrameItem from "../components/FrameItem.mjs";

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

export default function IndexPage(props) {
  const [modalShown, setModalShown] = useState(false);
  const modalRef = useRef();

  // Store frames in state so that we can update the UI when a frame is deleted
  const [frames, setFrames] = useState(props.frames);

  const showQRCode = (url) => {
    const modalContent = modalRef.current.querySelector(".content");
    modalContent.innerHTML = "";

    const div = document.createElement("div");
    div.style.border = "10px solid white";
    modalContent.append(div);

    setModalShown(true);
    new QRCode(div, url);
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
      ${frames.map(
        (frame) => html`<${FrameItem}
          frame=${frame}
          onShowQRCode=${showQRCode}
          onDelete=${deleteFrame}
        />`
      )}
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
