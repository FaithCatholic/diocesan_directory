# Diocesan Directory

Provides a `directory` content entity for the organisations a diocese wants to
publish: parishes, schools, agencies and cemeteries. Each of those is a bundle
(a "Directory category", itself a `directory_type` config entity) with its own
fields for name, addresses, phone, fax, email, website, county, year
established, parent and child organisations, and a few bundle-specific ones
such as church type, rite, languages and grades. Entries are revisionable and
translatable, have a published flag, an owner, a public page at
`/directory/{id}` and an admin listing under Content.

## Requirements

Drupal 10.3+ or 11, plus:

- [Address](https://www.drupal.org/project/address)
- [Field Group](https://www.drupal.org/project/field_group)
- Core Link, Telephone, Text, Options, User and Node.

The shipped `admin_directory` view restricts access by role and therefore
depends on three roles existing on the site before the module is installed:
`administrator`, `diocesan_administrator` and `directory`. On a fresh site
only `administrator` exists, and enabling the module fails with an unmet
config dependency until the other two are created. The role labels do not
matter, only the machine names.

## Installation

Install as usual at /admin/extend. If it refuses with a dependency error,
create the `diocesan_administrator` and `directory` roles first (for example
`drush role:create diocesan_administrator 'Diocesan Administrator'` and
`drush role:create directory 'Directory'`), then install again.

Installation creates the four categories (agencies, cemeteries, parishes,
schools), their field storage, field instances, form and view displays, and
the admin view.

## Usage

- Directory listings: /admin/manage/directory (the view, also linked as
  "Directory listings" under Content). The plain entity list is at
  /admin/content/directory.
- Add an entry: /admin/content/directory/add, or one of the category links in
  the admin menu. Edit, delete and revisions live under
  /admin/content/directory/{id}/.
- Categories and their fields: /admin/structure/directory_type (Field UI is
  enabled per category). Creating a category creates an empty bundle; the
  fields shipped with the module are only attached to the four default
  categories.
- Public page: /directory/{id}, rendered by `templates/directory.html.twig`
  with the usual suggestions (`directory--{bundle}`, `directory--{id}`,
  `directory--{bundle}--{view-mode}` and so on).
- Permissions: the module defines its own set under "Diocesan Directory",
  covering add, edit, delete, view published, view unpublished, access to the
  overview, and viewing, reverting and deleting revisions. "Administer
  Directory entities" grants all of it. Managing categories uses the core
  "Administer site configuration" permission.
- The name field on parish forms is widened to 255 characters by
  `hook_form_alter()`; every other bundle uses the field's own limit.

## Development

The repository ships a DDEV setup based on
[ddev-drupal-contrib](https://github.com/ddev/ddev-drupal-contrib) with the
[drupal-contrib-extras](https://github.com/hanoii/ddev-drupal-contrib-extras),
[pimp-my-shell](https://github.com/hanoii/ddev-pimp-my-shell) and
[ddev-drupal](https://github.com/hanoii/ddev-drupal) add-ons.

- `ddev start` runs the bootstrap on first start: `poser` builds a Drupal 11
  project in `web/` and `vendor/` (both git-ignored), installs the site,
  symlinks this module into `web/modules/custom/diocesan_directory`, creates
  the two roles the view needs and enables the module with its dependencies.
  Every start prints a one-time login link; the account is `admin` with
  password `1`.
- `ddev phpcs` and `ddev phpcbf` use the Drupal ruleset in `phpcs.xml.dist`.
- `ddev phpstan --level=6 -c ../../../../phpstan.neon` is the static analysis
  command to use. Plain `ddev phpstan` does not pick up the repository's
  `phpstan.neon`, because the add-on symlinks it into the module directory
  with a broken relative path, so it runs at level 0 with no ignores and
  reports `new static()` noise.

## Known issues and notes

- `DefaultEntitySettingsForm` is referenced by
  `DefaultEntityHtmlRouteProvider::getSettingsFormRoute()`, but that method
  only registers the `directory.settings` route for entity types without a
  bundle entity type. Because the `directory` entity declares
  `bundle_entity_type = "directory_type"`, the route is never added and the
  form is unreachable. It is kept in case a settings route is wanted later.
- The two entity types still use `@ContentEntityType` and `@ConfigEntityType`
  annotations. They should move to PHP attributes before Drupal 12.
- The procedural functions in `diocesan_directory.module` only exist so the
  hooks still fire on Drupal 10. Once the minimum is Drupal 11.1 they can be
  deleted; `src/Hook/DiocesanDirectoryHooks.php` is the implementation.
- `DefaultEntityRevisionRevertForm::getDescription()` returns an empty string
  where the interface documents `TranslatableMarkup`. Core's own node form
  does the same; phpstan reports it at level 6 and it is left as is.
