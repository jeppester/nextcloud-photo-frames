export default function renderSmartFittedImage(image, canvas) {
  // 25% is exactly what is needed to fit a 4/3 image into a 16/9 frame
  const cropMax = 0.25;
  const [wW, wH] = [canvas.offsetWidth, canvas.offsetHeight];
  const ratio = image.width / image.height;
  const windowRatio = wW / wH;
  const aspectDiff = ratio - windowRatio;

  let croppedHeight = image.height;
  let croppedWidth = image.width;

  if (aspectDiff > 0) {
    // Crop width
    const widthDiff = aspectDiff / ratio;
    const cropAmount = Math.min(cropMax, widthDiff);
    croppedWidth -= image.width * cropAmount;
  } else {
    // Crop height
    const heightDiff = Math.abs(aspectDiff) / windowRatio;
    const cropAmount = Math.min(cropMax, heightDiff);
    croppedHeight -= image.height * cropAmount;
  }

  canvas.width = 0;
  canvas.height = 0;
  canvas.width = wW * (window.devicePixelRatio || 1);
  canvas.height = wH * (window.devicePixelRatio || 1);

  const ctx = canvas.getContext("2d");
  ctx.save();
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  ctx.translate(wW / 2, wH / 2);
  const scale = aspectDiff > 0 ? wW / croppedWidth : wH / croppedHeight;

  ctx.scale(scale, scale);

  // const offsetX = -Math.round((image.width - croppedWidth) / 2);
  // const offsetY = -Math.round((image.height - croppedHeight) / 2);
  // ctx.drawImage(image, offsetX, offsetY);
  ctx.drawImage(image, -image.width / 2, -image.height / 2);
  ctx.restore();
}
