import Script from "next/script";

/** Primary pub from ads.txt / layout meta (Auto ads). */
const PUB = "4403691045202329";

/**
 * Load AdSense after the page is ready so first paint and menu taps stay fast.
 * Overlay formats (vignette / anchor) are controlled in the AdSense UI, not here —
 * hiding or intercepting those layers fights Google and can break Close.
 */
export default function GoogleAdSense() {
  return (
    <Script
      async
      src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-${PUB}`}
      crossOrigin="anonymous"
      strategy="lazyOnload"
    />
  );
}
