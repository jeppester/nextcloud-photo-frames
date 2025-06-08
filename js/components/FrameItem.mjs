import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

import CopyButton from "../components/CopyButton.mjs";
import Actions from "../components/Actions.mjs";
import Schedule from "./Schedule.mjs";
import Screen from "./Screen.mjs";

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
    align-items: flex-start;

    @media (max-width: calc(550px + 3rem)) {
      flex-direction: column;
      align-items: stretch;
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
  info: css`
    flex-grow: 1;
    align-self: stretch;
    display: flex;
    flex-direction: column;
  `,
  infoHeading: css`
    display: flex;
    gap: 1rem;
    align-items: flex-start;
  `,
  preview: css`
    max-width: 50%;

    @media (max-width: calc(550px + 3rem)) {
      max-width: none;
    }
  `,
  iframeContainer: css`
    width: 100%;
    aspect-ratio: 16/10;
    overflow: hidden;
  `,
  iframe: css`
    width: 300%;
    height: 300%;
    transform: scale(33.3333%);
    transform-origin: 0% 0%;
  `,
};

export default function FrameItem(props) {
  const { frame, onShowQRCode, onDelete } = props;

  return html`
    <div className=${styles.frame}>
      <${Screen} className=${styles.preview}>
        <div className=${styles.iframeContainer}>
          <iframe className=${styles.iframe} src=${urlForFrame(frame)} />
        </div>
      <//>
      <div className=${styles.info}>
        <div className=${styles.infoHeading}>
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
        <${Schedule} className="grow" ...${frame} />
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
