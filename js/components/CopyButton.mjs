import {
  html,
  useEffect,
  useState,
} from "../vendor/htm-preact-standalone.min.mjs";

export default function CopyButton(props) {
  const { children, copiedText, data, ...rest } = props;
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    let timeout;
    if (copied) {
      timeout = setTimeout(() => setCopied(false), 1000);
      return () => clearTimeout(timeout);
    }
  }, [copied]);

  const handleClicked = async () => {
    await navigator.clipboard.writeText(data);
    setCopied(true);
  };

  return html`
    <button onClick=${handleClicked} ...${rest}>
      ${copied ? copiedText : children}
    </button>
  `;
}
