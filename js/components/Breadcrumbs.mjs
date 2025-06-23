import { css } from "../vendor/emotion-css.min.mjs";
import { html } from "../vendor/htm-preact-standalone.min.mjs";

const styles = {
  breadcrumbs: css`
    position: sticky;
    top: -1.5rem;
    margin: -1.5rem;
    margin-bottom: 0;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    background-color: var(--color-main-background);
    box-shadow: 0px 5px 10px var(--color-main-background);
    z-index: 1;
  `,
  items: css`
    flex: 1 1 auto;
    width: 1%;
  `,
  parents: css`
    display: flex;
    align-items: center;
    gap: 0.5rem;
  `,
  parent: css`
    color: var(--color-primary);
    text-decoration: underline;
  `,
  current: css`
    margin: 0;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  `,
};

export default function Breadcrumbs(props) {
  const { items, ...restProps } = props;
  const parents = props.items.slice();
  const current = parents.pop();

  return html`
    <div className=${styles.breadcrumbs}>
      <div className=${styles.items}>
        <div className=${styles.parents} ...${restProps}>
          ${parents.map(
            (item) => html`
              <a className=${styles.parent} href=${item.url}>${item.title}</a>
              ${" > "}
            `
          )}
        </div>
        <h2 className=${styles.current}>${current.title}</h2>
      </div>
      ${props.children}
    </div>
  `;
}
