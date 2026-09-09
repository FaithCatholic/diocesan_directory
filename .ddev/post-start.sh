#!/bin/bash
set -e -o pipefail

./.ddev/commands/web/poser

# If db empty, do initial setup
if [ -z "$(drush status --field=bootstrap)" ]; then
  drush si --site-name=diocesan-directory --account-pass=1 -y
  # This normally is take care by config.contrib.yaml but if this was run with
  # freshly cloned project, poser needs to be run manually, but we are automating
  # that
  ./.ddev/commands/web/symlink-project
  # The shipped admin_directory view depends on these roles from the client
  # site, so they must exist before the module's config can be installed.
  drush role:create diocesan_administrator 'Diocesan Administrator'
  drush role:create directory 'Directory'
  drush en -y devel address field_group diocesan_directory
fi

# Always output this
drush uli
