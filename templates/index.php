<?php
declare(strict_types=1);
use OCA\PhotoFrame\Db\FrameMapper;
?>

<div class="app-content">
  <h2>Photo Frames</h2>

  <table>
    <thead>
      <th>Name</th>
      <th>Album</th>
      <th>Selection</th>
      <th>Rotation</th>
      <th>Day start/end</th>
      <th></th>
    </thead>
    <tbody>
      <?php foreach ($_['frames'] as $frame): ?>
        <tr>
          <td><?php echo $frame->getName() ?></td>
          <td><?php echo $frame->getAlbumName() ?></td>
          <td>
            <?php
            echo [
              FrameMapper::SELECTION_METHOD_LATEST => "Latest",
              FrameMapper::SELECTION_METHOD_OLDEST => "Oldest",
              FrameMapper::SELECTION_METHOD_RANDOM => "Random"
            ][$frame->getSelectionMethod()];
            ?>
          </td>
          <td>
            <?php
            echo [
              FrameMapper::ENTRY_LIFETIME_ONE_HOUR => "1 per hour",
              FrameMapper::ENTRY_LIFETIME_1_4_DAY => "4 per day",
              FrameMapper::ENTRY_LIFETIME_1_3_DAY => "3 per day",
              FrameMapper::ENTRY_LIFETIME_1_2_DAY => "2 per day",
              FrameMapper::ENTRY_LIFETIME_ONE_DAY => "1 per day"
            ][$frame->getEntryLifetime()];
            ?>
          </td>
          <th><?php echo $frame->getStartDayAt() ?> to <?php echo $frame->getEndDayAt() ?></th>
          <th>
            <a target="_BLANK" href="/index.php/apps/photoframe/<?php echo $frame->getShareToken() ?>">
              <button>Show frame</button>
            </a>
            <a target="_BLANK" href="/index.php/apps/photoframe/<?php echo $frame->getId() ?>/edit">
              <button>Edit frame</button>
            </a>
            <form data-delete data-confirm="Are you sure that you want to delete the frame"
              action="/index.php/apps/photoframe/<?php echo $frame->getId() ?>">
              <button>Delete frame</button>
            </form>
          </th>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="/index.php/apps/photoframe/new">
    <button>Create a new frame</button>
  </a>
</div>

<script type="text/javascript" nonce="<?php echo $_['cspNonce']; ?>">
  [...document.querySelectorAll('form[data-confirm]')].forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
        return;
      }

      if (form.hasAttribute('data-delete')) {
        event.preventDefault();
        const response = await fetch(form.action, { method: 'DELETE' })
        if (response.ok) form.closest('tr').remove();
      }
    })
  })
</script>
