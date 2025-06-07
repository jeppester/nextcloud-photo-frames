import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

import CopyButton from "../components/CopyButton.mjs";
import Actions from "../components/Actions.mjs";
import Schedule from "./Schedule.mjs";

const urlForFrame = ({ shareToken }) =>
  location.origin +
  generateUrl("apps/photo_frames/{shareToken}", { shareToken });

const styles = {
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
  `,
};

export default function FrameItem(props) {
  const { frame, onShowQRCode, onDelete } = props;

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

          <button className="error" onClick=${() => onDelete(frame)}>
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
        <${Schedule} ...${frame} />
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
          <button onClick=${() => onShowQRCode(urlForFrame(frame))}>
            Show QR
          </button>
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
}
