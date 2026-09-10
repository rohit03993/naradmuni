import Script from "next/script";

/** Both pubs from live ads.txt / old PHP (Auto ads). */
const PUBS = ["4403691045202329", "9363577326773521"] as const;

export default function GoogleAdSense() {
  return (
    <>
      {PUBS.map((id) => (
        <Script
          key={id}
          async
          src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-${id}`}
          crossOrigin="anonymous"
          strategy="afterInteractive"
        />
      ))}
    </>
  );
}
