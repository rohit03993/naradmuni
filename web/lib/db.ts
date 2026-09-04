import mysql from "mysql2/promise";

const host = process.env.DB_HOST || "localhost";
const port = Number(process.env.DB_PORT || 3306);

const pool = mysql.createPool({
  host,
  port,
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASSWORD ?? "",
  database: process.env.DB_NAME || "thenaradmunicom_db",
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 50,
  charset: "utf8mb4",
  connectTimeout: 8000,
  enableKeepAlive: true,
  // XAMPP on Windows: TCP to localhost is more reliable than named pipes
  socketPath: undefined,
});

export async function query<T = mysql.RowDataPacket>(
  sql: string,
  params: (string | number)[] = []
): Promise<T[]> {
  try {
    const [rows] = await pool.execute(sql, params);
    return rows as T[];
  } catch (err) {
    const msg = err instanceof Error ? err.message : String(err);
    throw new Error(
      `MySQL failed (${host}:${port} user=${process.env.DB_USER || "root"}). ` +
        `In XAMPP, MySQL must show Running. Detail: ${msg || "(no detail — usually MySQL is stopped or wrong password)"}`
    );
  }
}

export async function pingDb(): Promise<boolean> {
  try {
    await query("SELECT 1 AS ok");
    return true;
  } catch {
    return false;
  }
}
