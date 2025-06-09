import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import nPhotos from "../utils/nPhotos.mjs";

const styles = {
  time: css`
    font-family: monospace;
  `,
};

export default function Schedule(props) {
  if (props.rotationUnit === "day" && parseInt(props.rotationsPerUnit) === 1) {
    return html`<p className=${props.className || ""}>
      <strong>Schedule:</strong><br />Show one photo per day
    </p> `;
  }

  let rotationDescription;
  if (props.rotationUnit === "day") {
    rotationDescription = `Split time between ${nPhotos(
      props.rotationsPerUnit
    )}`;
  } else {
    rotationDescription = `Show ${nPhotos(props.rotationsPerUnit)} per ${
      props.rotationUnit
    }`;
  }

  return html`
    <p className=${props.className || ""}>
      <strong>Schedule:</strong><br />
      ${props.startDayAt == "00:00" && props.endDayAt == "00:00"
        ? html`All day: ${rotationDescription}`
        : html`
            ${props.startDayAt !== "00:00" &&
            html`
              <span className=${styles.time}> 00:00-${props.startDayAt}:</span>
              ${" "}"Pre-show" first photo<br />
            `}
            <span className=${styles.time}>
              ${props.startDayAt}-${props.endDayAt}:
            </span>
            ${" "}${rotationDescription}<br />
            ${props.endDayAt !== "00:00" &&
            html`
              <span className=${styles.time}> ${props.endDayAt}-00:00:</span>
              ${" "}Keep showing the last photo
            `}
          `}
    </p>
  `;
}
