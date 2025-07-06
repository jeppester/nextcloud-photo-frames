import { css, keyframes } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  container: css`
    position: fixed;
    display: flex;
    height: 100%;
    width: 100%;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  `,
  message: css`
    border-radius: 0.5rem;
    font-family: sans-serif;
    margin: 1rem 1rem;
    padding: 2.2rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 18rem;

    background-color: rgba(255, 255, 255, 0.8);

    color: #333;
    box-shadow: 0 0.5rem 3rem rgba(0, 0, 0, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.5);
  `,
  heading: css`
    margin: 0;
    font-size: 2.2rem;
  `,
  info: css`
    margin: 0;
    line-height: 1.5;
  `,
  button: css`
    padding: 0.6rem 1rem;
    color: #eee;
    margin-top: 0.6rem;
    background-color: #252525;
    /* box-shadow: 0 0 1rem rgba(0, 0, 0, 0.2); */
    /* font-size: 1rem; */
    border: 2px solid #000;
    border-radius: 0.4rem;
    align-self: flex-end;
  `,
};

export default function EmptyAlbum() {
  return html`
    <div className=${styles.container}>
      <div className=${styles.message}>
        <h1 className=${styles.heading}>Album is empty</h1>
        <p className=${styles.info}>
          Add some photos to the album, or pick a different album, then reload
          the frame.
        </p>
        <button onClick=${() => location.reload()} className=${styles.button}>
          Reload
        </button>
      </div>
    </div>
  `;
}
