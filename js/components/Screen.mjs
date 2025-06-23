import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  screen: css`
    font-size: 33.33%;
    width: 100%;
    padding: 1.5rem;
    background-color: #111;
    border: 2px solid #888;
    border-radius: 1rem;

    @media (prefers-color-scheme: dark) {
      background-color: #000;
      border: 2px solid #444;
    }
  `,
  screenInner: css`
    position: relative;
    overflow: hidden;
    border-radius: 0.1rem;
  `,
  screenContents: css`
    aspect-ratio: 16/10;
    width: 100%;
    container-type: size;
  `,
  textScaler: css`
    // 16px on a 1280px wide display, e.g. 16 / 1280 * 100 = 1.25
    font-size: 1.25cqw;
  `,
};

export default function Screen(props) {
  return html`
    <div className=${`${styles.screen} ${props.className || ""}`}>
      <div className=${styles.screenInner}>
        <div className=${styles.screenContents}>
          <div className=${styles.textScaler}>${props.children}</div>
        </div>
      </div>
    </div>
  `;
}
