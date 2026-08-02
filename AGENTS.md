# KumbiaPHP Agent Guide

Use this repository-wide guide to make focused, compatible, and verifiable
changes. Treat tracked project files and observed source behavior as the source
of truth; do not infer capabilities or commands from unrelated local tooling.

## Purpose and scope

KumbiaPHP is a lightweight PHP MVC framework with two main boundaries: the
shared framework engine in `core/` and the default application template in
`default/`. Keep changes within the boundary requested by the task and validate
every boundary they affect.

This guide covers repository inspection, implementation, and validation. It is
not an API manual and does not replace the tracked configuration, tests, or
source code.

## Quick workflow

1. Inspect the affected files, nearby tests, and tracked authorities before
   editing.
2. Record existing worktree changes and leave unrelated files untouched.
3. Make the smallest change that satisfies the task while preserving supported
   PHP versions and public behavior.
4. Run the focused test first, then the suite for each affected boundary.
5. Review changed paths, diff quality, and documentation claims before handing
   off the work.

For changing facts, consult these authorities rather than copying assumptions:

| Topic | Tracked authority |
|---|---|
| PHP and development dependencies | `composer.json` |
| Active CI versions and commands | `.github/workflows/phpunit.yml` |
| Core test bootstrap and suite | `core/phpunit.xml.dist`, `core/tests/` |
| Application-template test bootstrap and suite | `default/app/phpunit.xml.dist`, `default/app/tests/` |
| Runtime behavior | Relevant tracked source and its nearby tests |

## Repository map

| Path | Purpose |
|---|---|
| `core/kumbia/` | Framework bootstrap, routing, controllers, configuration, views, and autoloading |
| `core/libs/` | Framework libraries, generally organized by library name |
| `core/tests/` | Core framework tests and their bootstrap |
| `default/app/` | Default application template: controllers, models, views, libraries, and configuration |
| `default/app/tests/` | Application-template tests and test helpers |
| `default/public/` | Default web entry point and public assets |
| `.github/workflows/` | Active continuous-integration workflows |

The normal web request starts at `default/public/index.php`, which defines the
application, core, and public paths and loads `core/kumbia/bootstrap.php`. The
bootstrap loads framework services, dispatches through `Router`, and renders
through `View`. Verify details against the current source before changing this
flow.

## Setup and verified commands

Run commands from the repository root. Install the dependencies declared by
Composer:

```bash
composer install --prefer-dist
```

Run the core framework suite:

```bash
vendor/bin/phpunit --configuration core/phpunit.xml.dist
```

Run the default application-template suite:

```bash
vendor/bin/phpunit --configuration default/app/phpunit.xml.dist
```

These are the repository-supported setup and test commands. Do not invent or
prescribe formatter, linter, static-analysis, console, or additional Composer
commands without tracked evidence.

## Change-to-test matrix

| Change scope | Required validation |
|---|---|
| `core/` only | Run the most focused relevant core test, then the core suite. |
| `default/` only | Run the most focused relevant application test, then the application-template suite. |
| Both `core/` and `default/` | Validate focused behavior and run both suites. |
| Tests or PHPUnit configuration | Run the suite controlled by the changed test boundary; run both if both boundaries are affected. |
| Documentation only | Check structure, links or claims against tracked authorities, whitespace, changed paths, and scope. Runtime tests are optional unless the documentation change accompanies behavior. |

Do not use a passing suite from one boundary as proof for changes in the other.
When a focused test is unavailable, run the complete affected suite and state
that limitation in the handoff.

## Conventions

- Preserve the `php >=8.0` compatibility declared in `composer.json`. Active CI
  exercises PHP 8.0 through 8.5; do not introduce syntax outside that range.
- Match nearby code structure, naming, formatting, and test style. Avoid broad
  modernization in a targeted change.
- Existing documentation and comments are primarily Spanish. Preserve the
  language and tone of the surrounding context when editing them.
- Treat framework classes, helpers, configuration keys, routing behavior, and
  generated output as potential public API. Check callers and tests before
  changing signatures or observable behavior.
- When creating an application, keep application-specific behavior in
  `default/app/` and use object-oriented extension points rather than modifying
  `core/`. Change `core/` only when the framework itself needs the behavior.
- Add or update focused tests with behavior changes. Assertions should prove
  outcomes, not implementation details.
- Prefer repository-relative paths in documentation and verify every technical
  claim against tracked files.

## Security considerations

- Never commit credentials, API keys, tokens, private keys, or production
  configuration values. Treat untracked local configuration as sensitive unless
  a tracked authority explicitly says otherwise.
- Treat request data as untrusted. Validate the expected type, range, and
  business rules at the boundary; filtering alone is not authorization or a
  substitute for context-appropriate output escaping.
- Enforce authentication and authorization for every protected action or
  resource. Do not rely on routes, navigation, or client-side controls as the
  access boundary; verify the relevant source and tests when changing it.
- Escape dynamic view output for its rendered context, and avoid exposing
  sensitive values in responses, exceptions, fixtures, or logs.
- Treat changes to `composer.json` and dependency lockfiles as security
  sensitive. Keep versions compatible with the tracked PHP requirement, review
  the dependency's purpose and maintenance status, and do not prescribe
  vulnerability-scanning commands without tracked support.

## Safety and completion checklist

### Safety boundaries

- Keep the edit set targeted; do not overwrite, clean up, or reformat unrelated
  worktree changes.
- Do not silently change public APIs, compatibility requirements, CI behavior,
  or application defaults.
- Do not treat legacy CI configuration as active authority or reconcile stale
  CI signals unless the task explicitly requests it.
- Do not duplicate large manuals or attempt to document every API here. Link a
  stable tracked path and summarize only what an agent needs to act safely.
- Never claim a tool or workflow is supported merely because it is conventional
  for a PHP project. Require evidence in tracked configuration or active CI.
- Avoid source-mutating formatters or normalizers unless the repository
  explicitly configures them and the task requires their use.

### Before handoff

- [ ] The changed paths match the requested scope.
- [ ] Existing unrelated changes remain untouched.
- [ ] Every documented command and compatibility claim has a tracked authority.
- [ ] Focused validation and all affected test boundaries have been considered.
- [ ] Public behavior and compatibility risks are called out explicitly.
- [ ] The final diff contains no accidental whitespace or generated artifacts.
