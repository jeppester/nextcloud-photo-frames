import { html } from "../vendor/htm-preact-standalone.min.mjs";
import { generateUrl } from "../vendor/nextcloud-router.min.mjs";
import Breadcrumbs from "../components/Breadcrumbs.mjs";
import FrameFields from "../components/FrameFields.mjs";

export default function EditPage({ frame, requestToken, albums }) {
  const breadcrumbItems = [
    { title: "Photo frames", url: generateUrl("apps/photo_frames") },
    { title: frame.name }
  ]

  return html`
    <>
      <form action=${generateUrl("apps/photo_frames/{id}", { id: frame.id })} method="post">
        <${Breadcrumbs} items=${breadcrumbItems}>
          <button type="submit" class="primary">Save frame</button>
        <//>
        <${FrameFields}
          albums=${albums}
          frame=${frame}
          requestToken=${requestToken}
        >
      <//>
    <//>
  `;
}
