# Naradmuni — how to run (everything in the browser on :3000)

## What you open (remember only these)

| Open in Chrome | What it is |
|---|---|
| **http://localhost:3000/** | Public website (new Next.js design) |
| **http://localhost:3000/news/{newsurl}** | Article (same path as production SEO) |
| **http://localhost:3000/category/{cat_url}** | Category |
| **http://localhost:3000/login** | **Only** admin login |
| **http://localhost:3000/admin** | Admin dashboard (after login) |

Do **not** open `127.0.0.1:8080` or `localhost:8080` in the browser.
Old PHP homepage / article skins now **redirect** to `:3000`. Files are kept; the old UI is not shown.

## Why Apache :8080 still runs in XAMPP

It is the **engine** (PHP admin + `/images/...`). Next.js on 3000 **proxies** those requests. You never need to type 8080.

## One admin login

- Login: `http://localhost:3000/login` (same as `/naradmuni/manage.php`)
- After login → dashboard
- Logged-out admin pages also go to that same login (not the old site)

## Start

1. XAMPP: start **Apache** + **MySQL**
2. Then:
```bat
cd /d E:\Softwares DEV- Chiki\naradmuni\web
npm run go
```
3. Use only **http://localhost:3000/**

## SEO note

Article URLs stay `/news/{newsurl}`. Image paths stay `/naradmuni/images/...` (or production domain later). We do not invent new slug schemes.
