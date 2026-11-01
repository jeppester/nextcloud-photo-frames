import { css, keyframes } from "../vendor/emotion-css.min.mjs";
import {
  html,
  useEffect,
  useRef,
} from "../vendor/htm-preact-standalone.min.mjs";

export const CLOCK_SIZE_MIN = 50;
export const CLOCK_SIZE_MAX = 300;

const animations = {
  blink: keyframes`
    from { transform: scaleX(1) }
    to { transform: scaleX(-1) }
  `,
};

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    align-items: start;
  `,
  time: css`
    line-height: 1;
    text-align: left;
    text-transform: capitalize;
    margin: 0;

    color: #fff;
    text-shadow: -1px -1px 2px rgba(0, 0, 0, 0.2),
      1px -1px 2px rgba(0, 0, 0, 0.2), -1px 1px 2px rgba(0, 0, 0, 0.2),
      1px 1px 2px rgba(0, 0, 0, 0.2);
    font-size: 2.4em;
    font-weight: 700;

    .separator {
      display: inline-block;
    }

    &[data-style="flip"] .separator {
      animation: ${animations.blink} 1000ms ease-in-out infinite;
    }
  `,
};

const clampSize = (size) => {
  const sizeNumber = Number(size);
  if (!Number.isFinite(sizeNumber)) return 100;
  return Math.min(CLOCK_SIZE_MAX, Math.max(CLOCK_SIZE_MIN, sizeNumber));
};

export default function Clock({ clockStyle = "flip", clockSize = 100 }) {
  const hoursRef = useRef();
  const minutesRef = useRef();

  useEffect(() => {
    const updateTime = () => {
      const now = new Date();

      hoursRef.current.innerHTML =
        clockStyle === "plain"
          ? `${now.getHours()}`.padStart(2, "0")
          : now.getHours();
      minutesRef.current.innerHTML = `${now.getMinutes()}`.padStart(2, "0");
    };

    const interval = setInterval(updateTime, 1000);
    updateTime();

    return () => clearInterval(interval);
  });

  return html`
    <div
      className=${styles.container}
      style=${{ fontSize: `${clampSize(clockSize)}%` }}
    >
      <h1 className=${styles.time} data-style=${clockStyle}>
        <span ref=${hoursRef} className="hours"></span>
        <span className="separator">${clockStyle === "plain" ? ":" : "."}</span>
        <span ref=${minutesRef} className="minuts"></span>
      </h1>
    </div>
  `;
}
