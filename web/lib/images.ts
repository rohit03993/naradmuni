const ASSET = (process.env.NEXT_PUBLIC_ASSET_BASE || "http://localhost:8080/naradmuni").replace(/\/$/, "");

export function newsImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/images/news/${encodeURIComponent(file)}`;
}

export function teamImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/team/${encodeURIComponent(file)}`;
}

export function adImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/ads/${encodeURIComponent(file)}`;
}

export function logoSrc(): string {
  return `${ASSET}/images/logo/${encodeURI("Logo @2x.png")}`;
}

export function asset(path: string): string {
  return `${ASSET}${path.startsWith("/") ? path : `/${path}`}`;
}
