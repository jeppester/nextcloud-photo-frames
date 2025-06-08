import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  screen: css`
    font-size: 33.3333%;
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
};

export default function Screen(props) {
  return html`
    <div className=${`${styles.screen} ${props.className || ""}`}>
      <div className=${styles.screenInner}>
        <div style=${{ aspectRatio: "16/10" }}>${props.children}</div>
      </div>
    </div>
  `;
}
