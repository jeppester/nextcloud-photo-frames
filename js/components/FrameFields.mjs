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
import Screen from "./Screen.mjs";

const rotationsOptionsForUnit = {
  day: [1, 2, 3, 4, 6, 8, 12],
  hour: [1, 2, 3, 4, 6, 10, 15, 20, 30],
  minute: [1, 2, 3, 4, 6],
};

const getClosestOption = (options, current) =>
  options.find((option) => option >= current) || options.at(-1);

const styles = {
  frameFields: css`
    display: flex;
    flex-direction: row;
    width: 100%;
    max-width: 1600px;
    gap: 3rem;

    @media screen and (max-width: 800px) {
      flex-direction: column;
      gap: 0;
    }

    > * {
      flex: 1 1 1%;
    }

    input,
    select {
      height: auto;
    }
  `,
  detailColumns: css`
    max-width: 30rem;
    display: flex;
    gap: 1rem;

    > * {
      flex: 1 1 1%;
    }

    input,
    select {
      width: 100%;
    }
  `,
  radioButtons: css`
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
  `,
  fieldTitle: css`
    margin-top: 1rem;
    margin-bottom: 0.3rem;
    font-size: 1.2rem;
    font-weight: 600;

    & + * {
      margin-top: 0 !important;
    }
  `,
  previewColumn: css`
    display: flex;
    flex-direction: column;

    @media screen and (max-width: 768px) {
      flex-flow: column-reverse;
    }
  `,
  preview: css`
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
  `,
  error: css`
    color: var(--color-error);
  `,
  // Keeping this for now
  // screensBottom: css`
  //   width: 100%;
  //   display: flex;
  //   gap: 0.5rem;
  // `,
  // screensLeft: css`
  //   display: flex;
  //   flex-direction: column;
  //   gap: 0.5rem;
  //   width: calc((100% - 4rem - 0.5rem - 8px) / 26 * 16 + 2rem);
  // `,
  screen: css`
    padding: 1rem;
    max-width: 400px;

    @media screen and (min-width: 600px) {
      max-width: 600px;
    }
    @media screen and (min-width: 1400px) {
      max-width: 600px;
    }

    /* Keeping this around so I don't have to calculate again */
    /* &.screen1 {
      width: 100%;
    }
    &.screen2 {
      width: calc((100% - 4rem - 0.5rem - 8px) / 26 * 10 + 2rem);
    }
    &.screen3 {
      width: calc((100% - 4rem - 0.5rem - 8px) / 22.25 * 6.25 + 2rem);
    }
    &.screen4 {
      width: calc((100% - 4rem - 0.5rem - 8px) / 22.25 * 16 + 2rem);
    } */
  `,
};

const testImageLandscape = {
  url: `${window.appPath}/img/landscape.jpg`,
  timestamp: new Date(),
};
const testImagePortrait = {
  url: `${window.appPath}/img/portrait.jpg`,
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
    showPhotoTimestamp:
      frame.showPhotoTimestamp !== null ? frame.showPhotoTimestamp : true,
    showClock: frame.showClock !== null ? frame.showClock : false,
    photoSize: frame.photoSize || "smart-crop",
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
    <div className=${styles.frameFields}>
      <div>
        <input type="hidden" name="requesttoken" value="${requestToken}" />

        <div className=${styles.detailColumns}>
          <div>
            <h3 className=${styles.fieldTitle}>Name</h3>
            <input
              name="name"
              placeholder="Fill in a name"
              required
              className=${styles.nameInput}
              value=${data.name}
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
              <option value="" disabled>Choose album</option>
              ${albums.map(
                (album) => html`
                  <option key=${album.id} value=${album.id}>
                    ${album.title}
                  </option>
                `
              )}
            </select>
          </div>
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
      </div>

      <div className=${styles.previewColumn}>
        <div className=${styles.preview}>
          <${Screen} className=${styles.screen}>
            <${Frame}
              showPhotoTimestamp=${data.showPhotoTimestamp}
              showClock=${data.showClock}
              photoSize=${data.photoSize}
              image=${testImageLandscape}
            />
          <//>
          <${Screen} className=${styles.screen}>
            <${Frame}
              showPhotoTimestamp=${data.showPhotoTimestamp}
              showClock=${data.showClock}
              photoSize=${data.photoSize}
              image=${testImagePortrait}
            />
          <//>
        </div>
        <div>
          <h3 className=${styles.fieldTitle}>Display options</h3>
          <div>
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
          </div>

          <div>
            <label>
              <input
                type="checkbox"
                name="showClock"
                value="1"
                checked=${data.showClock}
                onChange=${handleInput}
              />
              <span>Show clock</span>
            </label>
          </div>

          <div className=${styles.radioButtons}>
            <label>
              <input
                type="radio"
                name="photoSize"
                value="smart-crop"
                required
                checked=${data.photoSize === "smart-crop"}
                onChange=${handleInput}
              />
              <span>
                <strong>Smart crop</strong>: Attempt to fit frame, but keep at
                least 75% of the photo visible</span
              >
            </label>

            <label>
              <input
                type="radio"
                name="photoSize"
                value="contain"
                required
                checked=${data.photoSize === "contain"}
                onChange=${handleInput}
              />
              <span>
                <strong>Contain</strong> the full photo within the frame
              </span>
            </label>
            <label>
              <input
                type="radio"
                name="photoSize"
                value="cover"
                required
                checked=${data.photoSize === "cover"}
                onChange=${handleInput}
              />
              <span>
                <strong>Cover</strong> the full frame, scaling proportionally
              </span>
            </label>
            <label>
              <input
                type="radio"
                name="photoSize"
                value="stretch"
                required
                checked=${data.photoSize === "stretch"}
                onChange=${handleInput}
              />
              <span>
                <strong>Stretch</strong> photo to the edges of the frame
              </span>
            </label>
          </div>
        </div>
      </div>
    </div>
  `;
}
