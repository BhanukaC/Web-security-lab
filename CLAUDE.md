# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A teaching lab of five intentionally paired demos, each showing a classic web vulnerability side-by-side with its fix. The lab runs as a Docker Compose stack rather than loose scripts.

- `demos/01-sql-injection/` — `vulnerable.php` concatenates `$_POST` directly into SQL; `secure.php` fixes it with prepared statements.
- `demos/02-xss/` — stored XSS; `vulnerable.php` echoes stored comments unescaped, `secure.php` escapes with `htmlspecialchars`.
- `demos/03-csrf/` — `vulnerable.php` accepts POSTed transfers with no verification (paired with `attacker.html`, a self-submitting forged form); `secure.php` adds a per-session CSRF token checked with `hash_equals()`.
- `demos/04-prompt-injection/` — a FastAPI chatbot ("ShopLK support"). `app.py` (port 8000) is vulnerable to direct and indirect prompt injection and improper output handling; `secure_app.py` (port 8001) is the fixed counterpart. Both call into `llm.py`, a shared provider layer — see below.
- `demos/05-secrets/` — `leaky-config.php` hardcodes a committed AWS key shaped to trigger gitleaks; the point is for gitleaks/CI to catch it, not to fix the file.

Demos 01-03 are plain procedural PHP (`mysqli`), no framework or build step. Demo 04 is Python/FastAPI. Demo 05 is a single static PHP file used as a secrets-scanning target.

The `demos/NN-name/{vulnerable,secure}` naming pairing (or `app.py`/`secure_app.py` for 04) is deliberate: if you touch one variant, check the contrast with its counterpart is still legible.

## The `llm.py` provider layer (demo 04)

`app.py` and `secure_app.py` never call an LLM SDK directly — they call `generate(system, user, tools)` in `llm.py`, which dispatches on the `LLM_PROVIDER` env var:

- `fake` (default) — deterministic, regex-based responses, no API key needed, no network calls. Used in CI and for anyone without a key.
- `gemini` — calls `google.genai` using `GEMINI_API_KEY` / `GEMINI_MODEL`.
- `anthropic` — calls the `anthropic` SDK using `ANTHROPIC_API_KEY`, including a tool-use round trip for the refund-tool demo.

All three modes implement the same interface, so the vulnerability/fix behavior is provider-independent — the injection payloads work against `fake` the same way they'd work against a real model.

## Running the lab

```bash
cp .env.example .env       # sets LLM_PROVIDER=fake by default
docker compose up
```

This starts three services: `web` (Apache/PHP serving `demos/` at http://localhost:8080), `db` (MySQL 8, seeded once from `db/init.sql` on first volume creation), and `chatbot` (demo 04's FastAPI app, vulnerable on :8000, secure on :8001). `docker compose down -v` drops the DB volume if you need `init.sql` to re-run.

There are no application-level automated tests — verification is manual, by exercising the vulnerable vs. secure endpoint and observing the difference. CI (`.github/workflows/security.yml`) runs a ZAP baseline scan and gitleaks, plus `.pre-commit-config.yaml`/`.gitleaksignore` gate secrets locally.

## Working conventions

- Keep the vulnerable/secure pairing intact across all five demos.
- The vulnerable files are deliberately insecure and must not be "fixed" unless asked — this now includes demo 04's `app.py` (prompt injection, improper output handling) and demo 05's `leaky-config.php` (the committed AWS key), not just the original PHP demos.
- DB credentials and the demo 05 AWS key are intentionally hardcoded/plaintext for a local lab environment — not patterns to carry into non-lab code.
- `.gitleaksignore` allowlists only the intentional demo secrets; a new hardcoded secret elsewhere should still fail the `secrets` CI job.
