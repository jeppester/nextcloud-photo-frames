import { html } from "../vendor/htm-preact-standalone.min.mjs";
import Alert from "../components/Alert.mjs";

export default function EditPage({ message, reportLink }) {
  return html`
    <h2>Photo Frames</h2>
    <${Alert} type="danger">${message}</p>

    ${
      Boolean(reportLink) &&
      html`
        <p>
          If you keep seeing this error, please report it${" "}
          <strong>
            <u><a target="_BLANK" href=${reportLink}>here</a></u>
          </strong>
        </p>
      `
    }
  `;
}
