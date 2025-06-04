import {
  html,
  useState,
  useEffect,
} from "../vendor/htm-preact-standalone.min.mjs";
import Frame from "./Frame.mjs";
import { css } from "../vendor/emotion-css.min.mjs";

const rotationsOptionsForUnit = {
  day: [1, 2, 3, 4, 6, 8, 12],
  hour: [1, 2, 3, 4, 6, 10, 15, 20, 30],
  minute: [1, 2, 3, 4, 6],
};

const getClosestOption = (options, current) =>
  options.find((option) => option >= current) || options.at(-1);

const styles = {
  radioButtons: css`
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
  `,
  fieldTitle: css`
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
    font-weight: 600;

    & + * {
      margin-top: 0 !important;
    }
  `,
  preview: css`
    margin-top: 1rem;
  `,
  screen: css`
    font-size: 30%;
    width: 100%;
    max-width: 20rem;
    padding: 1.5rem;
    background-color: #222;
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

const testImage = {
  url: `${window.appPath}/img/1000x750.svg`,
  timestamp: new Date(),
};

export default function FrameForm(props) {
  const { children, frame, albums, requestToken, ...rest } = props;

  // Fields
  const [data, setData] = useState({
    name: frame.name || "",
    albumId: frame.albumId || "",
    selectionMethod: frame.selectionMethod || "latest",
    showPhotoTimestamp: !!frame.showPhotoTimestamp,
    rotationUnit: frame.rotationUnit || "day",
    rotationsPerUnit: frame.rotationsPerUnit || 1,
    startDayAt: frame.startDayAt || "07:00",
    endDayAt: frame.endDayAt || "22:00",
  });

  const handleInput = ({ target: { name, value, checked, type } }) => {
    setData((prev) => ({
      ...prev,
      [name]: type === "checkbox" ? checked : value,
    }));
  };

  // Rotation options
  const rotationsOptions = rotationsOptionsForUnit[data.rotationUnit];

  // Update rotations options when unit changes
  useEffect(() => {
    const rotationsPerUnit = getClosestOption(
      rotationsOptions,
      parseInt(data.rotationsPerUnit)
    );
    setData((prev) => ({ ...prev, rotationsPerUnit }));
  }, [data.rotationUnit]);

  return html`
    <form ...${rest}>
      <div className="row">
        <div className="col">
          <input type="hidden" name="requesttoken" value="${requestToken}" />

          <div>
            <h3 className=${styles.fieldTitle}>Name</h3>
            <input
              name="name"
              placeholder="Pick a name for your photo frame"
              required
              value="${data.name}"
              onInput=${handleInput}
            />
          </div>

          <div>
            <h3 className=${styles.fieldTitle}>Album</h3>
            <select
              name="albumId"
              required
              value="${data.albumId}"
              onChange=${handleInput}
            >
              <option value="" disabled>Choose an album</option>
              ${albums.map(
                (album) => html`
                  <option key=${album.id} value=${album.id}>
                    ${album.title}
                  </option>
                `
              )}
            </select>
          </div>

          <div>
            <h3 className=${styles.fieldTitle}>Selection method</h3>
            <div className=${styles.radioButtons}>
              <label>
                <input
                  type="radio"
                  name="selectionMethod"
                  value="latest"
                  required
                  checked=${data.selectionMethod === "latest"}
                  onChange=${handleInput}
                />
                <span> Pick the <strong>latest</strong> photo</span>
              </label>
              <label>
                <input
                  type="radio"
                  name="selectionMethod"
                  value="oldest"
                  required
                  checked=${data.selectionMethod === "oldest"}
                  onChange=${handleInput}
                />
                <span> Pick the <strong>oldest</strong> photo </span>
              </label>
              <label>
                <input
                  type="radio"
                  name="selectionMethod"
                  value="random"
                  required
                  checked=${data.selectionMethod === "random"}
                  onChange=${handleInput}
                />
                <span> Pick a <strong>random</strong> photo </span>
              </label>
            </div>
            <p>
              Each frame keeps a record of previously shown photos. When a photo
              expires the next photo is chosen, from the remaining photos, using
              the specified selection method.
            </p>
          </div>

          <div>
            <h3 className=${styles.fieldTitle}>Photo rotation</h3>
            <p>Decide how often the photo should change.</p>
            <p>
              Per${" "}
              <select
                name="rotationUnit"
                required
                value="${data.rotationUnit}"
                onChange=${handleInput}
              >
                <option value="day">day</option>
                <option value="hour">hour</option>
                <option value="minute">minute</option>
              </select>
              ${" "}I would like to see${" "}
              <select
                name="rotationsPerUnit"
                value="${data.rotationsPerUnit}"
                onChange=${handleInput}
              >
                ${rotationsOptions.map(
                  (value) => html`
                    <option key=${value} value=${value}>${value}</option>
                  `
                )}
              </select>
              ${" "}photo(s)
            </p>
          </div>

          <div>
            <h3 className=${styles.fieldTitle}>Day start / end</h3>
            <p>
              Narrow down the time frame at which the photo will rotate. E.g.:
            </p>
            <ul>
              <li>Day start: 06:00</li>
              <li>Day end: 18:00</li>
              <li>Rotation: 3 photos/day</li>
            </ul>
            <p>Causes the frame to change at:</p>
            <ul>
              <li><strong>00:00</strong>: Photo 1 (before interval)</li>
              <li><strong>06:00</strong>: Photo 1</li>
              <li><strong>10:00</strong>: Photo 2</li>
              <li><strong>14:00</strong>: Photo 3</li>
              <li><strong>18:00</strong>: Photo 3 (after interval)</li>
            </ul>
            <div className="flex">
              <div>
                <p><strong>Day starts at</strong></p>
                <input
                  type="time"
                  name="startDayAt"
                  value="${data.startDayAt}"
                  required
                  onChange=${handleInput}
                />
              </div>
              <div>
                <p><strong>Day ends at</strong></p>
                <input
                  type="time"
                  name="endDayAt"
                  value="${data.endDayAt}"
                  required
                  onChange=${handleInput}
                />
              </div>
            </div>
          </div>
        </div>

        <div className="col">
          <div>
            <h3 className=${styles.fieldTitle}>Display options</h3>
            <label>
              <input
                type="checkbox"
                name="showPhotoTimestamp"
                value="1"
                checked=${data.showPhotoTimestamp}
                onChange=${handleInput}
              />
              <span>Show photo date</span>
            </label>

            <div className=${styles.preview}>
              <div className=${styles.screen}>
                <div className=${styles.screenInner}>
                  <div style=${{ aspectRatio: "16/10" }}>
                    <${Frame}
                      showPhotoTimestamp=${data.showPhotoTimestamp}
                      image=${testImage}
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      ${children}
    </form>
  `;
}
