# 01: SQL Injection

This demo contains a deliberately vulnerable login form. Do not deploy it anywhere public.

## What is broken

`vulnerable.php` builds a SQL query by concatenating the username and password fields
directly into a string. It also compares passwords as plaintext. An attacker can change
the meaning of the query without knowing any valid password.

## Payload to try

Username:

```
admin'#
```

Leave the password field empty and submit.

## Why it works

The query becomes `SELECT * FROM users WHERE username='admin'#' AND password=''`.
In MySQL, `#` starts a comment that runs to the end of the line, so the password
check is dropped. The database only checks that a user named `admin` exists, which
it does. MySQL runs whatever string it is given, it has no way to tell which parts
of that string came from a web form. `#` is used here instead of `--` because MySQL
requires a trailing space after `--` for it to start a comment (`admin' -- ` with the
space works too, but that trailing space is easy to lose to markdown rendering or
copy-paste, silently breaking the payload), whereas `#` has no such requirement.

## The fix

See `secure.php` lines 14-16: the query uses `mysqli_prepare()` with a `?` placeholder,
so user input is sent as data, never as part of the SQL text. Line 21 compares the
submitted password with `password_verify()` against the stored `password_hash` column,
and line 22 calls `session_regenerate_id(true)` after a successful login to prevent
session fixation.
