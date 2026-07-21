# 03: Cross-Site Request Forgery (CSRF)

Deliberately vulnerable money transfer form. Do not deploy it anywhere public.

## What is broken

`vulnerable.php` accepts a money transfer as a plain `GET` request with no token proving
the request came from the bank's own page. Any request that reaches that URL with the
right query string moves money, no matter where the request came from.

## Payload to try

Open `vulnerable.php` once in the browser so a session exists, then open `attacker.html`
in a new tab in the same browser:

```
<img src="http://localhost:8080/03-csrf/vulnerable.php?to=hacker&amount=1000" style="display:none">
```

Check the `transfers` table afterwards, a row appears sending 1000 to `hacker`.

## Why it works

Browsers attach cookies to every request to a site, including requests triggered by a
completely different page, such as an `<img>` tag loading a URL on the bank site.
Because the transfer only checks that a session exists and reads `to`/`amount` from the
query string, a hidden image tag on any other page the victim visits is enough to
trigger a transfer while looking like a normal image load.

## The fix

See `secure.php` lines 11-12: a random per-session token is generated with
`bin2hex(random_bytes(16))` and stored server-side. Lines 19-21 check the submitted
token with `hash_equals()` and reject the request if it does not match. The form is
also `POST` only, so a plain `<img>` tag cannot trigger it at all, and line 3 sets
`SameSite=Strict` on the session cookie so the browser will not even attach it to a
cross-site request.
