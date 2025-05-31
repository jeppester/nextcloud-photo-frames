import { html } from "../vendor/htm-preact-standalone.min.mjs";
import FrameForm from "../components/FrameForm.mjs";
import Actions from "../components/Actions.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";

export default function EditPage({ frame, requestToken, albums }) {
  return html`
    <h2>Edit ${frame.name}</h2>
    <${FrameForm}
      albums=${albums}
      frame=${frame}
      requestToken=${requestToken}
      action=${generateUrl("apps/photo_frames/{id}", { id: frame.id })}
      method="post"
    >
      <${Actions}>
        <div class="grow"></div>
        <a href=${generateUrl("apps/photo_frames")} class="button">Back</a>
        <button type="submit" class="primary">Update frame</button>
      <//>
    <//>
  `;
}
