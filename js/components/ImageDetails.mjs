import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    align-items: start;
    gap: 0.6em;
    filter: drop-shadow(-1px -1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(1px -1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(-1px 1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.2));
  `,
  detail: css`
    line-height: 1;
    text-align: left;
    text-transform: capitalize;
    margin: 0;
    color: #fff;
  `,
  camera: css`
    display: inline-block;
    height: 1em;
    fill: white;
    vertical-align: baseline;
    margin-right: 0.1em;
    margin-left: 0.1em;
    padding-bottom: 0.2em;
    border-bottom: 2px solid white;
  `,
  month: css`
    font-size: 1.8em;
    font-weight: 600;
    margin-right: 0.018em;
    margin-bottom: 0.02em;
  `,
  year: css`
    font-size: 2.4em;
    font-weight: 600;
  `,
  place: css`
    font-family: "Noto Serif", serif;
    font-size: 2.8em;
    font-weight: 500;
  `,
};

export default function ImageDate({ image, showTimestamp, showPlace }) {
  if (!showTimestamp && !showPlace) return null;

  return html`
    <div className=${styles.container}>
      <svg
        className=${styles.camera}
        version="1.1"
        viewBox="48 96 416 320"
        xml:space="preserve"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="m430.4 147h-67.5l-40.4-40.8s-0.2-0.2-0.3-0.2l-0.2-0.2c-6-6-14.1-9.8-23.3-9.8h-84c-9.8 0-18.5 4.2-24.6 10.9v0.1l-39.5 40h-69c-18.6 0-33.6 14.6-33.6 33.2v202.1c0 18.6 15 33.7 33.6 33.7h348.8c18.5 0 33.6-15.1 33.6-33.7v-202.1c0-18.6-15.1-33.2-33.6-33.2zm-174.4 218.5c-50.9 0-92.4-41.6-92.4-92.6 0-51.1 41.5-92.6 92.4-92.6 51 0 92.4 41.5 92.4 92.6 0 51-41.4 92.6-92.4 92.6zm168.1-165c-7.7 0-14-6.3-14-14.1s6.3-14.1 14-14.1 14 6.3 14 14.1-6.3 14.1-14 14.1z"
        />
        <path
          d="m256 210.84c-34.221 0-61.882 27.749-61.882 62.059 0 34.221 27.661 62.059 61.882 62.059 34.132 0 61.882-27.749 61.882-62.059s-27.749-62.059-61.882-62.059z"
        />
      </svg>

      <div
        style="display: flex; flex-direction: row; align-items: end; gap: 2em;"
      >
        ${showTimestamp &&
        html`
          <div>
            <h1 className=${`${styles.detail} ${styles.month}`}>
              ${" "}${Intl.DateTimeFormat(navigator.locale, {
                month: "short",
              })
                .format(image.timestamp)
                .replace(".", "")}
            </h1>

            <h1 className=${`${styles.detail} ${styles.year}`}>
              ${Intl.DateTimeFormat(navigator.locale, {
                year: "numeric",
              }).format(image.timestamp)}
            </h1>
          </div>
        `}
        ${showPlace &&
        html`
          <div>
            <h1 className=${`${styles.detail} ${styles.place}`}>
              ${image.place}
            </h1>
          </div>
        `}
      </div>
    </div>
  `;
}
