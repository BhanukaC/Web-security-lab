# Web Security Lab

> **Warning: intentionally vulnerable code.**
> This repository contains deliberately broken web applications for teaching. Run it
> only on localhost. Never deploy any of it to a public server. Do not copy patterns
> from any file named `vulnerable.*` or `app.py` into real projects.

## Quick start

```
git clone https://github.com/BhanukaC/Web-security-lab.git
cd Web-security-lab
cp .env.example .env
docker compose up
```

The default `.env` sets `LLM_PROVIDER=fake`, so the prompt injection demo works with no
API key. Once the containers are up, demos 01-03 are at http://localhost:8080, the
vulnerable chatbot is at http://localhost:8000, and the secure chatbot is at
http://localhost:8001.

## Demos

| Demo | Vulnerability | OWASP 2025 category | URL |
|---|---|---|---|
| `01-sql-injection` | SQL injection, plaintext passwords | A05 Injection | http://localhost:8080/01-sql-injection/vulnerable.php |
| `02-xss` | Stored cross-site scripting | A05 Injection | http://localhost:8080/02-xss/vulnerable.php |
| `03-csrf` | Cross-site request forgery | A01 Broken Access Control | http://localhost:8080/03-csrf/vulnerable.php |
| `04-prompt-injection` | Direct and indirect prompt injection | LLM01 Prompt Injection | http://localhost:8000/answer |
| `05-secrets` | Hardcoded credentials committed to git | A03 Software Supply Chain Failures | run gitleaks, see `demos/05-secrets/README.md` |

Each demo folder has its own README with the exact payload to try and where the fix
lives.

## Tooling

**OWASP ZAP baseline scan.** ZAP crawls a running site and reports common issues
without attacking it aggressively. Run it locally against the stack:

```
docker run --rm -t ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://host.docker.internal:8080
```

`host.docker.internal` resolves out of the box on Docker Desktop (Mac/Windows); on
native Linux Docker you'll need to add `--add-host=host.docker.internal:host-gateway`
to the command above, or just use your machine's LAN IP instead.

The same scan runs in CI against demos 01-03 and uploads an HTML report as a build
artifact.

**Burp Community as a proxy.** Set Burp's proxy listener to 127.0.0.1:8080 (its
default), point your browser at it through Burp's built-in browser or a proxy
extension, then browse the demos through Burp so you can see and replay each request.
Community edition has no active scanner, it is used here to intercept and repeat
requests, not to automate attacks.

**gitleaks.** Scans a git repository for secret-shaped strings, including ones that
were later deleted, since it reads history. Run
`docker run --rm -v "$PWD:/repo" zricethezav/gitleaks:v8.30.1 detect --source /repo --verbose`
locally, or rely on the `secrets` job in CI.

## Exercises

1. Bypass the login in demo 01 without knowing a valid password.
2. Steal a session cookie from demo 02 using a stored XSS payload.
3. Move money in demo 03 with a forged cross-site request from `attacker.html`.
4. Leak the staff discount code from demo 04's `/chat` endpoint with a direct prompt injection.
5. Trigger a refund from demo 04's `/summarise-review` endpoint with an indirect prompt injection hidden in a review.
6. Get arbitrary markup to execute through demo 04's `/answer` endpoint.
7. Get gitleaks to flag a secret in demo 05 that is not already in `.gitleaksignore`.

## Troubleshooting

**Port already in use.** Something else is listening on 8080, 8000, 8001, or 3306.
Stop that process, or change the left-hand side of the port mapping in
`docker-compose.yml`.

**Database not seeded on rerun.** `db/init.sql` only runs the first time a fresh MySQL
data volume is created. If you have already started the stack once, run
`docker compose down -v` to remove the volume, then `docker compose up` again.

**Docker on Windows.** Use WSL2 as the backend (Docker Desktop settings), and clone the
repository inside the WSL filesystem rather than under `/mnt/c`, file watching and
volume mounts are much slower across that boundary.

**Gemini returns a 429 error.** The free tier quota is shared across everyone using the
same Google Cloud project. If you are using a shared class key, wait a minute and
retry, or switch to your own key, or set `LLM_PROVIDER=fake` to keep working without
waiting.
