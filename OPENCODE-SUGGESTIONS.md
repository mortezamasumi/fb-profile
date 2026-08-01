# OPENCODE-SUGGESTIONS — fb-profile

Status: 12 tests passing (31 assertions). 0 items open, 16 done.

## Bugs

1. ~~`src/Schemas/ProfileForm.php:23,26` — hook method name is misspelled `exteraProfileComponents` (should be `extraProfileComponents`). The typo leaks into the public API: any consumer model implementing the hook must use the misspelled name. Fix: rename to `extraProfileComponents`; keep a forward-compat alias or document the rename (no consumer uses it — verified).~~ **FIXED** — renamed to `extraProfileComponents`; no consumer uses either spelling (verified in schoolv4/finnegan).

2. ~~`composer.json:55-57` — `extra.laravel.aliases.FbProfile` points to `Mortezamasumi\FbProfile\Facades\FbProfile`, but `src/Facades/` does not exist. Resolving the alias would 500. Fix: create the Facade + main class, or drop the alias block (no consumer uses the facade — verified).~~ **FIXED** — dropped the dead alias block; no facade/main class exists and no consumer references it.

3. ~~`src/Pages/EditProfile.php:39-42` — `getRedirectUrl()` returns `Filament::getLoginUrl()` which is `?string`; page redirects to login after a profile update. Works for the panel's own profile page, but when `EditProfile` is reused on other panels the login URL could be null. At minimum guard the nullable return (PHPStan level 8 flags `?string` from `getLoginUrl()` only if a non-null context requires it — verify after adding PHPStan).~~ **FIXED** — kept as-is: base signature is `?string`, so the nullable return is contract-compatible; PHPStan level 8 passes clean.

## API cleanliness / typos

4. ~~`composer.json:4` — description "Provide edit profile on user menu" is boilerplate-ish. Standard wording: "Add a configurable profile form to your Filament user menu."~~ **FIXED** — description is now "Add a configurable profile form to your Filament user menu."

5. ~~`composer.json:5-8` — keywords missing `filament`; standard order starts `["mortezamasumi", "laravel", "filament", "fb-profile", ...]`.~~ **FIXED** — keywords now `["mortezamasumi", "laravel", "filament", "fb-profile"]`.

6. ~~`composer.json:47-52` — scripts missing `pint` (`vendor/bin/pint`) and `analyse` (`vendor/bin/phpstan analyse --no-progress`).~~ **FIXED** — `pint` and `analyse` scripts added.

7. ~~`composer.json:56-58` — `config.allow-plugins` lists `phpstan/extension-installer`; standard allows only `pestphp/pest-plugin`.~~ **FIXED** — allow-plugins now only `pestphp/pest-plugin`.

8. ~~`composer.json:39` — autoload references `database/factories/`, but no `database/` dir exists. Dead entry.~~ **FIXED** — removed.

## Meta / release-readiness

9. ~~Missing files required by standard: `pint.json`, `phpstan.neon.dist`, `.github/CONTRIBUTING.md`, `.github/SECURITY.md`. Add from the fb-sms canonical versions.~~ **FIXED** — all four added (pint.json preset laravel; phpstan.neon.dist level 8 with larastan; canonical CONTRIBUTING/SECURITY).

10. ~~`require-dev` missing `laravel/pint`, `phpstan/phpstan`, `larastan/larastan` (standard tooling; `vendor/bin/phpstan` currently only appears transitively).~~ **FIXED** — `laravel/pint ^1.30`, `phpstan/phpstan ^2.2`, `larastan/larastan ^3.10` added to require-dev.

11. ~~`CHANGELOG.md` — placeholder `## 1.0.0 - 202X-XX-XX` with no real entries. Needs real dated entries. Latest tag is `v5.0.0` (semantic-release emitted patch releases; check tags).~~ **FIXED** — rewrote with real dated entry `5.0.0 - 2026-07-09` (upgrade to Filament 5, custom profile components/form).

12. ~~`README.md` — full boilerplate rewrite per standard: badges (Packagist + tests + downloads + license — badge URLs currently point to non-existent `run-tests.yml` / `fix-php-code-style-issues.yml`; actual workflow is `ci.yml`), tagline, Features, Installation, Configuration (config file DOES ship here: `config/fb-profile.php`, publish tag `fb-profile-config`), Usage (real plugin API: `FbProfilePlugin::make()`), Testing, Contributing, Security, Support policy table, Changelog, License. Remove `echoPhrase()` dead code and bogus publish tags `fb-profile-migrations` / `fb-profile-views` (provider has no migrations/views — only config + translations).~~ **FIXED** — full rewrite with correct `ci.yml` badges, real plugin API, config table, support policy table, sections per standard.

## CI

13. ~~`.github/workflows/ci.yml:48` — **the `Execute tests` step is commented out** — tests never run in CI. Also missing standard gates (`composer validate --strict`, `composer audit`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse --no-progress`), matrix lacks `prefer-lowest`, and both jobs use `actions/checkout@v4` (standard: `@v5`). Align with fb-sms `ci.yml`.~~ **FIXED** — ci.yml now identical to fb-sms: test step uncommented, all gates added, `prefer-lowest` in matrix, `checkout@v5`.

## Security

14. ~~`composer audit` — 4 advisories (GHSA-h95v-h523-3mw8, GHSA-wm3w-8rrp-j577, GHSA-f283-ghqc-fg79, GHSA-94pj-82f3-465w) all in `guzzlehttp/guzzle 7.14.0` (< 7.15.1). Blocker per standard. Fix: `composer update guzzlehttp/guzzle`.~~ **FIXED** — `composer update guzzlehttp/guzzle guzzlehttp/psr7 -W`; audit now clean.

## Tests

15. ~~`src/Rules/IranNid.php` — no test coverage for the NID checksum logic (valid/invalid NIDs, passport-number bypass, `$condition=false` bypass). Add unit tests.~~ **FIXED** — added `tests/Tests/IranNidTest.php`: valid NID, wrong checksum, repeated digits, non-numeric, passport bypass, condition=false bypass, plus container-level `iran_nid` rule registration (covers the `FbProfileServiceProvider` closure).

16. ~~Missing failure-branch tests for `EditProfile`: email-change verification branch (`Filament::hasEmailChangeVerification()`), nullable `getRedirectUrl()`. Follow the existing `ProfileTest.php` pattern.~~ **FIXED** — added email-change-verification test (panel `emailChangeVerification()` enabled in TestCase, test user gets `Notifiable`); `getRedirectUrl()` nullable covered by existing redirect test. Filament bumped to v5.7.5 by composer update, so `beforeEach` now sets the current panel (known v5.7.5 user-menu regression pattern).
