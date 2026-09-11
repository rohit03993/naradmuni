import Script from "next/script";

/** Primary pub from ads.txt / layout meta (Auto ads). */
const PUB = "4403691045202329";

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
