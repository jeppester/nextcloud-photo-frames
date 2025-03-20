<div class="row">
  <div class="col"> 
    <input type="hidden" name="requesttoken" value="<?= OCP\Util::callRegister(); ?>" />

    <div>
      <h3 class="field-title">Name</h3>

      <input name="name" placeholder="Pick a name for your photo frame" required
        value="<?= $_['frame']->getName() ?>" />
    </div>

    <div>
      <h3 class="field-title">Album</h3>

      <select name="album_id" required>
        <option value="" disabled<?= $_['frame']->getAlbumId() ? '' : ' selected' ?>>Choose an album</option>
        <?php foreach ($_['albums'] as $album): ?>
          <option value="<?= $album->getId() ?>" <?= $_['frame']->getAlbumId() === $album->getId() ? ' selected' : '' ?>>
            <?php echo $album->getTitle() ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>

    <div>
      <h3 class="field-title">Selection method</h3>

      <div class="radio-buttons">
        <label>
          <input type="radio" name="selection_method" value="latest"
            required<?= $_['frame']->getSelectionMethod() === 'latest' ? ' checked' : '' ?> />
          <span>Pick the <strong>latest</strong> photo</span>
        </label>

        <label>
          <input type="radio" name="selection_method" value="oldest"
            required<?= $_['frame']->getSelectionMethod() === 'oldest' ? ' checked' : '' ?> />
          <span>Pick the <strong>oldest</strong> photo</span>
        </label>

        <label>
          <input type="radio" name="selection_method" value="random"
            required<?= $_['frame']->getSelectionMethod() === 'random' ? ' checked' : '' ?> />
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
        <input type="checkbox" name="show_photo_timestamp" value="1" <?= $_['frame']->getShowPhotoTimestamp() ? ' checked' : '' ?> />
        <span>Show photo date</span>
      </label>
    </div>
  </div>

  <div class="col">
    <div>
      <h3 class="field-title">Photo rotation</h3>

      <p>Decide how often the photo should change.</p>

      <p>
        <select name="entry_lifetime" required>
          <option value="" disabled<?= $_['frame']->getEntryLifetime() === null ? ' selected' : '' ?>>Choose a rotation frequency</option>option
          <option value="one_hour" <?= $_['frame']->getEntryLifetime() === 'one_hour' ? ' selected' : '' ?>>1 photo per hour</option>
          <option value="1_4_day" <?= $_['frame']->getEntryLifetime() === '1_4_day' ? ' selected' : '' ?>>4 photos per day</option>
          <option value="1_3_day" <?= $_['frame']->getEntryLifetime() === '1_3_day' ? ' selected' : '' ?>>3 photos per day</option>
          <option value="1_2_day" <?= $_['frame']->getEntryLifetime() === '1_2_day' ? ' selected' : '' ?>>2 photos per day</option>
          <option value="one_day" <?= $_['frame']->getEntryLifetime() === 'one_day' ? ' selected' : '' ?>>1 photo per day</option>
        </select>
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
          <input type="time" name="start_day_at" value="<?= $_['frame']->getStartDayAt() ?: '07:00' ?>" required />
        </div>

        <div>
          <p><strong>Day ends at</strong></p>
          <input type="time" name="end_day_at" value="<?= $_['frame']->getEndDayAt() ?: '22:00' ?>" required />
        </div>
      </div>
    </div>
  </div>
</div>