export default function nPhotos(n, showNumber = true) {
  return [
    showNumber ? n.toString() : false,
    parseInt(n) === 1 ? `photo` : `photos`,
  ]
    .filter(Boolean)
    .join(" ");
}
