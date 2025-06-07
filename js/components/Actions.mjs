import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const className = css`
  display: flex;
  margin-top: 1rem;
  gap: 0.5rem 1rem;
  flex-wrap: wrap;
`;

export default function Actions(props) {
  return html`<div className=${className} ...${props} />`;
}
