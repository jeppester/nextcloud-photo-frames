import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    align-items: start;
    filter: drop-shadow(-1px -1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(1px -1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(-1px 1px 2px rgba(0, 0, 0, 0.2))
      drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.2));
  `,
  date: css`
    line-height: 1;
    text-align: left;
    text-transform: capitalize;
    font-family: sans-serif;
    margin: 0;
    color: #fff;
  `,
  month: css`
    font-size: 1.6em;
    font-weight: 600;
    margin-right: 0.018em;
    margin-left: 0.018em;
    margin-bottom: 0.135em;
  `,
  camera: css`
    display: inline-block;
    height: 0.85em;
    fill: white;
    vertical-align: baseline;
    margin-right: 0.1em;
    margin-left: 0.08em;
  `,
  year: css`
    font-size: 2.7em;
    font-weight: 600;
  `,
};

export default function ImageDate({ image }) {
  return html`
    <div className=${styles.container}>
      <h1 className=${`${styles.date} ${styles.month}`}>
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
        ${" "}${Intl.DateTimeFormat(navigator.locale, {
          month: "short",
        })
          .format(image.timestamp)
          .replace(".", "")}
      </h1>

      <h1 className=${`${styles.date} ${styles.year}`}>
        ${Intl.DateTimeFormat(navigator.locale, {
          year: "numeric",
        }).format(image.timestamp)}
      </h1>
    </div>
  `;
}
