import { html } from "../vendor/htm-preact-standalone.min.mjs";

export default function RadioButtons(props) {
  const { options, name, value, required, onChange } = props;

  return html`
    ${options.map(
      (option) => html`
        <label>
          <input
            type="radio"
            name=${name}
            value=${option.value}
            required=${required}
            checked=${value === option.value}
            onChange=${onChange}
          />
          <span>${option.label}</span>
        </label>
      `
    )}
  `;
}
