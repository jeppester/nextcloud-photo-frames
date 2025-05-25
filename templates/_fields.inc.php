<div class="row">
  <div class="col">
    <input type="hidden" name="requesttoken" value="<?= OCP\Util::callRegister() ?>" />

    <div>
      <h3 class="field-title">Name</h3>

      <input name="name" placeholder="Pick a name for your photo frame" required
        value="<?= $_["frame"]->getName() ?>" />
    </div>

    <div>
      <h3 class="field-title">Album</h3>

      <select name="album_id" required>
        <option value="" disabled<?= $_["frame"]->getAlbumId()
          ? ""
          : " selected" ?>>Choose an album</option>
        <?php foreach ($_["albums"] as $album): ?>
          <option value="<?= $album->getId() ?>" <?= $_[
            "frame"
            ]->getAlbumId() === $album->getId()
              ? " selected"
              : "" ?>>
            <?php echo $album->getTitle(); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <h3 class="field-title">Selection method</h3>

      <div class="radio-buttons">
        <label>
          <input type="radio" name="selection_method" value="latest"
            required<?= $_["frame"]->getSelectionMethod() === "latest"
              ? " checked"
              : "" ?> />
          <span>Pick the <strong>latest</strong> photo</span>
        </label>

        <label>
          <input type="radio" name="selection_method" value="oldest"
            required<?= $_["frame"]->getSelectionMethod() === "oldest"
              ? " checked"
              : "" ?> />
          <span>Pick the <strong>oldest</strong> photo</span>
        </label>

        <label>
          <input type="radio" name="selection_method" value="random"
            required<?= $_["frame"]->getSelectionMethod() === "random"
              ? " checked"
              : "" ?> />
          <span>Pick a <strong>random</strong> photo</span>
        </label>
      </div>

      <p>
        Each frame keeps a record of previously shown photos. When a photo expires the next photo is chosen,
        from the remaining photos, using the specified selection method.
      </p>
    </div>

    <div>
      <h3 class="field-title">Display options</h3>

      <label>
        <input type="checkbox" name="show_photo_timestamp" value="1" <?= $_[
        "frame"
        ]->getShowPhotoTimestamp()
          ? " checked"
          : "" ?> />
        <span>Show photo date</span>
      </label>

      <br />

      <label>
        <input type="checkbox" name="style_fill" value="1" <?= $_[
        "frame"
        ]->getStyleFill()
          ? " checked"
          : "" ?> />
        <span>Crop photo to fill the frame</span>
      </label>

      <br />

      <label>
        <input type="checkbox" name="show_clock" value="1" <?= $_[
        "frame"
        ]->getShowClock()
          ? " checked"
          : "" ?> />
        <span>Show digital clock</span>
      </label>

      <br />

      <label>
        <span>Background color</span>
        <input type="color" name="style_background_color" value="<?= $_["frame"]->getStyleBackgroundColor() ?: '#222222' ?>" />
      </label>
    </div>
  </div>

  <div class="col">
    <div>
      <h3 class="field-title">Photo rotation</h3>

      <p>Decide how often the photo should change.</p>

      <p>
        Per
        <select name="rotation_unit" required>
          <option value="day" <?= in_array($_["frame"]->getRotationUnit(), [
            null,
            "day",
          ])
            ? "selected"
            : "" ?>>day</option>
          <option value="hour" <?= $_["frame"]->getRotationUnit() === "hour"
            ? "selected"
            : "" ?>>hour</option>
          <option value="minute" <?= $_["frame"]->getRotationUnit() === "minute"
            ? "selected"
            : "" ?>>minute</option>
        </select>
        I would like to see
        <select name="rotations_per_unit">
          <option value="<?= $_["frame"]->getRotationsPerUnit() ?: 1 ?>" selected>
            <?= $_["frame"]->getRotationsPerUnit() ?: 1 ?>
          </option>
        </select>
        photo(s)

        <script nonce="<?= $_['cspNonce']; ?>">
          function updateTimeOptions() {
            const rotationsPerMinuteElm = document.querySelector('[name="rotations_per_unit"]')
            let rotationsPerMinute = parseInt(rotationsPerMinuteElm.value)
            const unit = document.querySelector('[name="rotation_unit"]').value
            let timeOptions = []

            switch (unit) {
              case 'day':
                timeOptions.push(1, 2, 3, 4, 6, 8, 12)
                break
              case 'hour':
                timeOptions.push(1, 2, 3, 4, 6, 10, 15, 20, 30)
                break
              case 'minute':
                timeOptions.push(1, 2, 3, 4, 6)
                break
            }

            // Update the currently selected option to one of the available ones
            rotationsPerMinute = timeOptions.find((option) => option >= rotationsPerMinute)
            if (rotationsPerMinute === undefined) rotationsPerMinute = timeOptions.at(-1)

            const newOptions = timeOptions.map((value) => {
              const option = document.createElement('option')
              option.setAttribute('value', value)
              option.toggleAttribute('selected', value === rotationsPerMinute)
              option.innerText = value
              return option
            })

            rotationsPerMinuteElm.replaceChildren(...newOptions)
          }

          const rotationUnitElm = document.querySelector('[name="rotation_unit"]')
          rotationUnitElm.addEventListener('change', updateTimeOptions)
          updateTimeOptions()
        </script>
      </p>
    </div>

    <div>
      <h3 class="field-title">Day start / end</h3>

      <p>
        Narrow down the time frame at which the photo will rotate. E.g.:
      </p>

      <ul>
        <li>Day start: 06:00</li>
        <li>Day end: 18:00</li>
        <li>Rotation: 3 photos/day</li>
      </ul>

      <p>
        Causes the frame to change at:
      </p>

      <ul>
        <li><strong>00:00</strong>: Photo 1 (before interval)</li>
        <li><strong>06:00</strong>: Photo 1</li>
        <li><strong>10:00</strong>: Photo 2</li>
        <li><strong>14:00</strong>: Photo 3</li>
        <li><strong>18:00</strong>: Photo 3 (after interval)</li>
      </ul>

      <div class="flex">
        <div>
          <p><strong>Day starts at</strong></p>
          <input type="time" name="start_day_at" value="<?= $_[
          "frame"
          ]->getStartDayAt() ?:
            "07:00" ?>" required />
        </div>

        <div>
          <p><strong>Day ends at</strong></p>
          <input type="time" name="end_day_at" value="<?= $_[
          "frame"
          ]->getEndDayAt() ?:
            "22:00" ?>" required />
        </div>
      </div>
    </div>
  </div>
</div>