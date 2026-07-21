# 05: Leaked Secrets

`leaky-config.php` contains fake credentials shaped like a real AWS key and a real
database password, committed directly into the repository.

## What is broken

Credentials are hardcoded in a file that is committed to git. Anyone who can read the
repository, or a mirror, or a CI log, or a clone from years ago, can read them too.

## Payload to try

Run gitleaks against the repository:

```
docker run --rm -v "$PWD:/repo" zricethezav/gitleaks:latest detect --source /repo --verbose
```

It reports a finding in `leaky-config.php`.

## Why it works

git keeps every version of every file forever by default. Deleting `leaky-config.php`
in a new commit does not remove it from history, the old commit that added it still has
the full content, and anyone can check it out. A public repository makes this worse,
but even a private one is not safe once a credential has been pushed, because forks,
local clones, and CI caches can all still hold a copy.

## The fix

There is no code fix for a leaked secret. The only real fix is rotation: revoke the AWS
key and change the database password at the provider, so the leaked values stop
working, then remove them from the codebase going forward. Rewriting history to strip
the file (for example with `git filter-repo`) only helps once every clone and fork has
also been updated, so it is not a substitute for rotation. This repository allowlists
this one file in `.gitleaksignore` so CI stays green, the exercise is to trigger a
finding on a secret that is not allowlisted.
