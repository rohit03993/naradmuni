import { query } from "@/lib/db";

export type SiteSettingsMap = Record<string, string>;

export async function ensureSiteSettingsTable(): Promise<void> {
  await query(`
    CREATE TABLE IF NOT EXISTS site_settings (
      setting_key VARCHAR(64) NOT NULL,
      setting_value TEXT NOT NULL,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  `);
}

export async function getSiteSettings(keys: string[]): Promise<SiteSettingsMap> {
  if (!keys.length) return {};
  try {
    await ensureSiteSettingsTable();
  } catch {
    // Table create may fail on read-only user; still try SELECT
  }
  const placeholders = keys.map(() => "?").join(",");
  try {
    const rows = await query<{ setting_key: string; setting_value: string }>(
      `SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN (${placeholders})`,
      keys
    );
    const out: SiteSettingsMap = {};
    for (const row of rows) {
      out[row.setting_key] = row.setting_value;
    }
    return out;
  } catch {
    return {};
  }
}
