# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Official Emailchef Add On for Paid Memberships Pro plugin (slug: `emailchef-add-on-for-pmp`). It connects a Paid Memberships Pro membership site to Emailchef: WordPress users and PMPro members are subscribed as contacts to Emailchef lists, mapped per membership level, plus optional opt-in lists members can choose at PMPro checkout or from their profile.

Requires Paid Memberships Pro (activation aborts without it) and authenticates against Emailchef with API keys (consumer key / secret).

## Commands

Toolchain: Node 20 (`.nvmrc`) + pnpm 9 (pinned via `packageManager`, use through corepack: `corepack enable` once, then plain `pnpm` works). Node is only needed for the tooling below — the plugin itself has **no build step**: admin and public CSS/JS are plain files under `admin/` and `public/`, loaded as-is. There are no tests and no PHP linting configured; PHP is loaded directly by WordPress.

```bash
pnpm install
```

## Local dev environment (wp-env)

`@wordpress/env` (needs Docker) spins up WordPress with Paid Memberships Pro (latest stable) and the plugin mounted and activated; config in `.wp-env.json` (PHP 8.2, `WP_DEBUG` on).

```bash
pnpm run env:start    # http://localhost:8888 — admin: admin/password
pnpm run env:stop
pnpm run env:destroy  # remove containers + volumes
pnpm run env:cli ...  # WP-CLI inside the container, e.g. pnpm run env:cli option get pmproecaddon_plugin_user_enabled
```

## Architecture

The plugin follows the WordPress Plugin Boilerplate layout (loader + admin/public classes).

### Bootstrap (emailchef-add-on-for-pmp.php)

Defines `EMAILCHEF_ADD_ON_FOR_PMP_VERSION` and `EMAILCHEF_ADD_ON_FOR_PMP_PATH`, requires the API classes and `common-api.php`, registers activation/deactivation hooks, then instantiates `Emailchef_Add_On_For_Pmp` and calls `run()`.

`Emailchef_Add_On_For_Pmp_Activator::activate()` bails with `wp_die()` if `pmpro_hasMembershipLevel()` is missing. `Emailchef_Add_On_For_Pmp_Deactivator::deactivate()` deletes every `pmproecaddon_*` option — it is also what the "disconnect account" AJAX action calls.

### Core plugin (includes/class-emailchef-add-on-for-pmp.php)

`Emailchef_Add_On_For_Pmp` loads dependencies, sets the locale, and registers admin and public hooks through `Emailchef_Add_On_For_Pmp_Loader` (a simple collection of actions/filters flushed in `run()`). Both the admin and public classes receive their own `Emailchef_Add_On_For_Pmp_Api` instance, built from the stored `pmproecaddon_consumer_key` / `pmproecaddon_consumer_secret` options.

The PMPro-facing hooks (checkout sync, profile fields, `user_register`) are only registered when the `pmproecaddon_plugin_user_enabled` option is `yes`, i.e. after a successful API login.

### API client (includes/class-emailchef-add-on-for-pmp-api-base.php, includes/class-emailchef-add-on-for-pmp-api.php)

`Emailchef_Add_On_For_Pmp_Api_Base::call()` wraps `wp_remote_request()` against `https://app.emailchef.com/apps/api/v1` (base URL overridable with the `EMAILCHEF_API_URL` constant; request args filterable via `emailchef-addon-for-pmp_get_args`), authenticating with `consumerKey` / `consumerSecret` headers. Credentials can be swapped at runtime with `set()`, which returns `$this` for chaining (used by the login AJAX handler).

`Emailchef_Add_On_For_Pmp_Api` adds one method per operation — `account()`, `lists()`, `add_contact()` — through the private `json()` helper, which fires the `pmproecaddon_api_response` action, returns a `['status' => 'error', ...]` array on non-200 responses, and passes the decoded body through `pmproecaddon_response_body_success` / `pmproecaddon_response_body_error` filters.

### Admin (admin/class-emailchef-add-on-for-pmp-admin.php)

Registers the *Settings > PMPro Emailchef* options page (`pmproecaddon_options`), the plugin action link, and two nonce-protected AJAX endpoints: `emailchef-add-on-for-pmp_check_login` (validates the API keys via `account()`, then stores them and sets `pmproecaddon_plugin_user_enabled`) and `emailchef-add-on-for-pmp_disconnect`. `save_options()` (hooked on `admin_post_pmproecaddon_save_data`) maps the checkbox matrix from the settings form into the `pmproecaddon_plugin_list_config`, `pmproecaddon_plugin_list_opt_in_audiences` and `pmproecaddon_plugin_list_non_member` options, and redirects back with a `pmproecaddon_msg` query arg. It also renders and saves the opt-in lists shown on the user profile.

### Public (public/class-emailchef-add-on-for-pmp-public.php)

Subscribes members to Emailchef on `pmpro_after_checkout` and `user_register`: the membership level name is matched against `pmproecaddon_plugin_list_config` to resolve the level lists, and the `opt_in_audiences_<list>_checkbox` request fields resolve the opt-in lists. `handle_api_response()` (on `pmproecaddon_api_response`) flips `pmproecaddon_plugin_user_enabled` back to `no` on a 401, so an invalidated API key logs the plugin out.

### Shared markup (common-api.php)

`pmproecaddon_list_match_display()` renders the list checkbox matrix used both in the admin settings page and on the PMPro checkout / profile forms. Checkbox names are built as `<subscription_name>_<list_name>_checkbox` with spaces replaced by underscores — the same convention the admin and public classes use when reading `$_POST` / `$_REQUEST`.

### Options

All options are `pmproecaddon_`-prefixed: `consumer_key`, `consumer_secret`, `plugin_user_enabled`, `settings`, `plugin_list_config`, `plugin_list_opt_in_audiences`, `plugin_list_non_member`, `require_unsuscribe_on_level`, `require_update_profile`.

## Translations

All user-facing strings use the `emailchef-add-on-for-pmp` text domain; translations are delivered as language packs from translate.wordpress.org. The POT catalog lives in `languages/`.

## Deployment

Deployment to the WordPress.org SVN repo happens automatically via GitHub Actions (`.github/workflows/deploy.yml`) when a git tag is pushed; the workflow then attaches the packaged zip to a GitHub release for the tag — the exact zip deployed to WP.org. A build & package check workflow (`.github/workflows/build.yml`) does the same packaging on every PR and uploads the zip as an artifact. `.distignore` controls what is excluded from the deployed zip (dev files and repo metadata are excluded). `generate_archive.sh` builds a rough zip locally with `git archive`, but it does not honour `.distignore` — the CI artifact is the reference.

## Conventions

- Code style is WordPress-flavored PHP as in existing files; match the surrounding file.
- Everything written to the repo or GitHub is in **English**: PR titles and bodies, commit messages, code comments, and docs — regardless of the language used in conversation.
