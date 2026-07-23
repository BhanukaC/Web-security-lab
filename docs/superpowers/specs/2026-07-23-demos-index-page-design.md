# Demos index page

## Problem

`docker-compose.yml` mounts `./demos` directly as the Apache document root
(`web` service). There is no `index.php`/`index.html` at that root and
directory listing is off by default (`php:8.3-apache` image), so `GET /`
returns `403 Forbidden`.

This breaks ZAP baseline spidering when it's pointed at the bare root
(`zap-baseline.py -t http://host.docker.internal:8080`): the spider hits the
403 and stops, never discovering the real demo pages
(`/01-sql-injection/vulnerable.php`, etc.). The same limitation exists in
`.github/workflows/security.yml`'s `zap-baseline` job, which targets
`http://localhost:8080` — it doesn't fail the build (`-I` plus
`continue-on-error: true`), but its uploaded report is shallow for the same
reason.

## Solution

Add `demos/index.php`: a bare, unstyled list of links to every real demo
entry point served under the Apache document root:

- `01-sql-injection/vulnerable.php`
- `01-sql-injection/secure.php`
- `02-xss/vulnerable.php`
- `02-xss/secure.php`
- `03-csrf/vulnerable.php`
- `03-csrf/secure.php`
- `03-csrf/attacker.html`
- `05-secrets/leaky-config.php`

`04-prompt-injection` is out of scope — it's a separate FastAPI service on
ports 8000/8001, not served from `demos/`'s Apache document root.

No `sitemap.xml`. It doesn't affect crawl depth (ZAP treats a missing
sitemap as a harmless 404) and would be purely cosmetic.

## Rejected alternative

Apache directory listing (`.htaccess` with `Options +Indexes`) instead of an
index file. Rejected because ZAP's passive scanner has a dedicated rule,
`Directory Browsing [10033]`, that flags this exact configuration as a WARN
finding — using it to fix crawl depth would inject a fake vulnerability into
every scan report, undermining a lab whose value depends on trustworthy
findings.

## Effect

- `GET /` returns `200` instead of `403`.
- `zap-baseline.py -t http://host.docker.internal:8080` (or
  `http://localhost:8080` in CI) can now spider from root and reach all 8
  demo pages without needing a multi-target automation plan
  (`zap-full-scan.yaml`, added earlier in this session for the same
  purpose) just to get full coverage.
- No changes needed to `.github/workflows/security.yml`.

## Out of scope

- Styling the index page.
- Per-demo descriptions/captions.
- `sitemap.xml`.
- Linking `04-prompt-injection`.
- Any change to the vulnerable/secure pairing behavior of existing demo
  files.
