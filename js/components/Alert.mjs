import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const className = css`
  padding: 1rem;
  border: 1px solid;
  border-radius: 1rem;
  white-space: pre-wrap;

  &.danger {
    border-color: var(--color-error);
    color: var(--color-error);
  }

  &.info {
    border-color: var(--color-info);
    color: var(--color-info-text);
  }
`;

export default function Alert({ type, ...props }) {
  return html`<div className=${`${className} ${type || ""}`} ...${props} />`;
}
