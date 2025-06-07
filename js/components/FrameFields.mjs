import {
  html,
  useState,
  useEffect,
  useRef,
} from "../vendor/htm-preact-standalone.min.mjs";
import Frame from "./Frame.mjs";
import { css } from "../vendor/emotion-css.min.mjs";
import Schedule from "./Schedule.mjs";
import nPhotos from "../utils/nPhotos.mjs";

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
    font-size: 35%;
    width: 350px;
    max-width: 100%;
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
  error: css`
    color: var(--color-error);
  `,
};

const testImage = {
  url: `${window.appPath}/img/landscape.jpg`,
  timestamp: new Date(),
};

export default function FrameFields(props) {
  const { frame, albums, requestToken } = props;
  const startDayAtRef = useRef();

  // Fields
  const [data, setData] = useState({
    name: frame.name || "",
    albumId: frame.albumId || "",
    selectionMethod: frame.selectionMethod || "latest",
    showPhotoTimestamp: !!frame.showPhotoTimestamp,
    rotationUnit: frame.rotationUnit || "hour",
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

  const startEndIsInvalid =
    data.endDayAt !== "00:00" && data.endDayAt <= data.startDayAt;

  useEffect(() => {
    if (!startDayAtRef.current) return;

    startDayAtRef.current.setCustomValidity(
      startEndIsInvalid ? "Start time must be before end time" : ""
    );
  }, [startEndIsInvalid]);

  return html`
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
            Shown photos get discarded from the selection pool. When the pool
            runs dry, all discarded photos are readded to the pool.
          </p>
        </div>

        <div>
          <h3 className=${styles.fieldTitle}>Photo rotation</h3>
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
            ${" "} show${" "}
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
            ${" "}${nPhotos(data.rotationsPerUnit, false)}
          </p>
        </div>
      </div>

      <div className="col">
        <div>
          <h3 className=${styles.fieldTitle}>Day start / end</h3>
          <p>
            Use this to avoid "wasting" photos during the night and/or to better
            control each photo's interval when rotating per day.
          </p>
          ${data.rotationUnit === "day" && parseInt(data.rotationsPerUnit) === 1
            ? html`<p>
                <strong>
                  This option doesn't matter when the frame shows a single photo
                  per day
                </strong>
                <input type="hidden" name="startDayAt" value="07:00" />
                <input type="hidden" name="endDayAt" value="22:00" />
              </p>`
            : html`
                <p>
                  Rotate photos from${" "}
                  <input
                    type="time"
                    name="startDayAt"
                    value="${data.startDayAt}"
                    required
                    ref=${startDayAtRef}
                    onChange=${handleInput}
                  />
                  ${" "}until${" "}
                  <input
                    type="time"
                    name="endDayAt"
                    value="${data.endDayAt}"
                    required
                    onChange=${handleInput}
                  />
                </p>

                ${startEndIsInvalid
                  ? html`
                      <p className=${styles.error}>
                        Start time must be before end time
                      </p>
                    `
                  : html`<${Schedule} ...${data} />`}
              `}
        </div>

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
  `;
}
