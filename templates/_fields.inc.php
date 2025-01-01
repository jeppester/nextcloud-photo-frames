<input type="hidden" name="requesttoken" value="<?= OCP\Util::callRegister(); ?>" />

<div>
  <p class="field-title">Name</p>

  <input name="name" placeholder="Pick a name for your photo frame" required value="<?= $_['frame']->getName() ?>" />
</div>

<div>
  <p class="field-title">Album</p>

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
  <p class="field-title">Selection method</p>

  <p>This options decides how photos are chosen.</p>

  <p>
    Each frame keeps a record of previously shown photos. When a photo expires, the next photo will be chosen by
    using the configured selection method.
  </p>

  <p>
    When the frame runs out of photos it clears its record and starts over.
  </p>

  <label>
    <input type="radio" name="selection_method" value="latest" required<?= $_['frame']->getSelectionMethod() === 'latest' ? ' checked' : '' ?> />
    <span>Pick the <strong>latest</strong> photo</span>
  </label>

  <label>
    <input type="radio" name="selection_method" value="oldest" required<?= $_['frame']->getSelectionMethod() === 'oldest' ? ' checked' : '' ?> />
    <span>Pick the <strong>oldest</strong> photo</span>
  </label>

  <label>
    <input type="radio" name="selection_method" value="random" required<?= $_['frame']->getSelectionMethod() === 'random' ? ' checked' : '' ?> />
    <span>Pick a <strong>random</strong> photo</span>
  </label>
</div>

<div>
  <p class="field-title">Photo rotation</p>

  <p>
    Decide how often the photo should change.
  </p>

  <select name="entry_lifetime" required>
    <option value="" disabled<?= $_['frame']->getEntryLifetime() === null ? ' selected' : '' ?>>Choose a rotation frequency
    </option>
    <option value="one_hour" <?= $_['frame']->getEntryLifetime() === 'one_hour' ? ' selected' : '' ?>>1 photo per hour
    </option>
    <option value="1_4_day" <?= $_['frame']->getEntryLifetime() === '1_4_day' ? ' selected' : '' ?>>4 photos per day
    </option>
    <option value="1_3_day" <?= $_['frame']->getEntryLifetime() === '1_3_day' ? ' selected' : '' ?>>3 photos per day
    </option>
    <option value="1_2_day" <?= $_['frame']->getEntryLifetime() === '1_2_day' ? ' selected' : '' ?>>2 photos per day
    </option>
    <option value="one_day" <?= $_['frame']->getEntryLifetime() === 'one_day' ? ' selected' : '' ?>>1 photo per day
    </option>
  </select>
</div>

<div>
  <p class="field-title">Day start / end</p>

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
      <h5>Day starts at</h5>
      <input type="time" name="start_day_at" value="<?= $_['frame']->getStartDayAt() ?: '07:00' ?>" required />
    </div>

    <div>
      <h5>Day ends at</h5>
      <input type="time" name="end_day_at" value="<?= $_['frame']->getEndDayAt() ?: '22:00' ?>" required />
    </div>
  </div>
</div>
