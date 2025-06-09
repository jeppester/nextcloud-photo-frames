import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

import CopyButton from "../components/CopyButton.mjs";
import Schedule from "./Schedule.mjs";
import Screen from "./Screen.mjs";

const urlForFrame = ({ shareToken }) =>
  location.origin +
  generateUrl("apps/photo_frames/{shareToken}", { shareToken });

const styles = {
  frame: css`
    display: flex;
    flex-direction: column;

    p {
      font-size: 16px;
      font-weight: 500;
      margin: 0;
    }

    h2 {
      font-size: 1.4rem;
      text-align: center;
      font-weight: 600;
      letter-spacing: 0.06rem;
      margin-top: 0.7rem;
      margin-bottom: 1rem;
    }
  `,
  info: css`
    padding: 0 0.9rem;
    display: flex;
    flex-direction: column;
  `,
  actions: css`
    display: flex;
    gap: 0.2rem 0.2rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
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
        <h2>${frame.name}</h2>

        <div className=${styles.actions}>
          <a
            target="_BLANK"
            href=${generateUrl("apps/photo_frames/{shareToken}", {
              shareToken: frame.shareToken,
            })}
          >
            <button className="primary">Show</button>
          </a>
          <a
            href=${generateUrl("apps/photo_frames/{id}/edit", {
              id: frame.id,
            })}
          >
            <button>Edit</button>
          </a>
          <button onClick=${() => onShowQRCode(urlForFrame(frame))}>QR</button>
          <${CopyButton} data=${urlForFrame(frame)} copiedText="Copied">
            Copy link
          <//>

          <div className="grow" />

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
      </div>
    </div>
  `;
}
