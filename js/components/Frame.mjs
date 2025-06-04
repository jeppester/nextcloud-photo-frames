import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { css, keyframes } from "../vendor/emotion-css.min.mjs";

const animations = {
  fadeIn: keyframes`
    from { opacity: 0; }
    to { opacity: 100; }
  `,
};

const styles = {
  frame: css`
    background-color: #000;
    position: absolute;
    width: 100%;
    height: 100%;

    // Only animate when adding a frame on top of another frame
    & + & {
      animation: ${animations.fadeIn} 2s ease-in-out;
    }
  `,
  photoBackground: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: 100% 100%;
    filter: blur(6.25em) brightness(70%);
  `,
  photo: css`
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
  `,
  dateContainer: css`
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: start;
    padding: 1em;
  `,
  dateBackground: css`
    border-radius: 1em;
    border-top-left-radius: 0;
    border-bottom-right-radius: 0;
    padding: 1em 1em;
    display: flex;
    flex-direction: column;
    align-items: start;
    background-color: rgba(255, 250, 250, 0.4);
    border: 0.0625em solid rgba(213, 204, 195, 0.3);
    box-shadow: 0px 0.3125em 2, 5em rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(0.3125em);
  `,
  date: css`
    text-align: left;
    text-transform: capitalize;
    font-family: sans-serif;
    margin: 0;
    color: rgb(35, 18, 5);
    text-shadow: 0px 0px 0.0625em rgba(255, 255, 255, 0.4);
  `,
  dateSpacer: css`
    height: 0.09375em;
    margin: 0.25em 1em 0 0.1em;
    background-color: rgb(35, 18, 5);
  `,
  year: css`
    font-size: 2em;
    font-weight: 600;
  `,
  month: css`
    font-size: 1.2em;
    font-weight: 500;
    margin-left: 0.05em;
    padding-bottom: 0.2em;
    border-bottom: 0.09375em solid rgb(35, 18, 5);
  `,
};

export default function Frame(props) {
  const { showPhotoTimestamp, image } = props;

  return html`
    <div className=${styles.frame}>
      <div
        className=${styles.photoBackground}
        style=${{ backgroundImage: `url("${image.url}")` }}
      />
      <div
        className=${styles.photo}
        style=${{ backgroundImage: `url("${image.url}")` }}
      />

      ${showPhotoTimestamp &&
      html`
        <div className=${styles.dateContainer}>
          <div className=${styles.dateBackground}>
            <h1 className=${`${styles.date} ${styles.month}`}>
              ${Intl.DateTimeFormat(navigator.locale, {
                month: "short",
              }).format(image.timestamp)}
            </h1>
            <h1 className=${`${styles.date} ${styles.year}`}>
              ${Intl.DateTimeFormat(navigator.locale, {
                year: "numeric",
              }).format(image.timestamp)}
            </h1>
          </div>
        </div>
      `}
    </div>
  `;
}
