/**
 * Build real PNG favicon / PWA icons from a source image.
 * Usage (from web/): node scripts/make-pwa-icons.mjs [sourcePath]
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import sharp from "sharp";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const webRoot = path.join(__dirname, "..");
const pub = path.join(webRoot, "public");
const iconsDir = path.join(pub, "icons");
const brandDir = path.join(webRoot, "brand");

const candidates = [
  process.argv[2],
  path.join(brandDir, "pwa-icon-source.png"),
  path.join(brandDir, "pwa-icon-source.jpg"),
  "C:/Users/user/.cursor/projects/e-Softwares-DEV-Chiki-naradmuni/assets/naradmuni-icon-real.png",
  "C:/Users/user/.cursor/projects/e-Softwares-DEV-Chiki-naradmuni/assets/naradmuni-favicon.png",
].filter(Boolean);

const src = candidates.find((p) => fs.existsSync(p));
if (!src) {
  console.error("No source image found. Pass a path: node scripts/make-pwa-icons.mjs path/to/icon.png");
  process.exit(1);
}

fs.mkdirSync(iconsDir, { recursive: true });
fs.mkdirSync(brandDir, { recursive: true });

// Keep a copy in-repo so server builds can regenerate without Cursor paths.
const brandCopy = path.join(brandDir, "pwa-icon-source.png");
await sharp(src).png().toFile(brandCopy);

const base = await sharp(src)
  .resize(512, 512, { fit: "contain", background: { r: 0, g: 0, b: 0, alpha: 1 } })
  .png()
  .toBuffer();

const small = await sharp(base).resize(192, 192).png().toBuffer();

/** New filenames so browsers/PWA cannot keep serving the old cached icon. */
const outs = [
  [path.join(pub, "favicon.png"), base],
  [path.join(iconsDir, "nm-512.png"), base],
  [path.join(iconsDir, "nm-192.png"), small],
  [path.join(iconsDir, "app-icon.png"), base],
  [path.join(iconsDir, "icon-512.png"), base],
  [path.join(iconsDir, "icon-192.png"), small],
];

for (const [out, buf] of outs) {
  fs.writeFileSync(out, buf);
  const meta = await sharp(out).metadata();
  if (meta.format !== "png") {
    console.error(`NOT PNG: ${out}`);
    process.exit(1);
  }
  console.log(`OK ${path.relative(webRoot, out)} (${meta.format} ${meta.width}x${meta.height})`);
}

console.log("Done. Commit brand/ + public/favicon.png + public/icons/nm-*.png");
