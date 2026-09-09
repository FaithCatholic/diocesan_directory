#!/bin/bash
#ddev-generated
set -e -o pipefail

composer_root="${1:-}"
drupal_root="${2:-web}"

composer_root="${composer_root%/}"
drupal_root="${drupal_root%/}"

if [ "$composer_root" = "." ]; then
  composer_root=""
fi

if [ "$drupal_root" = "." ]; then
  drupal_root=""
fi

validate_relative_path() {
  local name="$1"
  local value="$2"

  case "$value" in
    /*|..|../*|*/..|*/../*)
      echo "$name must be a relative path below the project root: $value" >&2
      exit 1
      ;;
  esac
}

join_path() {
  local base="$1"
  local path="$2"

  if [ -n "$base" ]; then
    echo "$base/$path"
  else
    echo "$path"
  fi
}

drupal_path() {
  local path="$1"

  if [ -n "$drupal_root" ]; then
    join_path "$composer_root" "$drupal_root/$path"
  else
    join_path "$composer_root" "$path"
  fi
}

validate_relative_path "COMPOSER_ROOT" "$composer_root"
validate_relative_path "WR" "$drupal_root"

composer_file="$(join_path "$composer_root" "composer.json")"
if [ ! -f "$composer_file" ]; then
  echo "Missing composer.json: $composer_file" >&2
  exit 1
fi

targets=(
  "$(join_path "$composer_root" "vendor")"
  "$(drupal_path "core")"
  "$(drupal_path "modules/contrib")"
  "$(drupal_path "libraries")"
  "$(drupal_path "themes/contrib")"
  "$(drupal_path "profiles/contrib")"
  "$(join_path "$composer_root" "drush/contrib")"
)

is_unsafe_target() {
  case "$1" in
    ""|"."|".."|"/"|".git"|"web"|"modules"|"themes"|"profiles"|"libraries"|"drush")
      return 0
      ;;
  esac

  return 1
}

echo "Removing Composer-installed directories:"
for target in "${targets[@]}"; do
  echo "  $target"
done
echo

for target in "${targets[@]}"; do
  if is_unsafe_target "$target"; then
    echo "Refusing unsafe target: $target" >&2
    exit 1
  fi

  if [ -e "$target" ] || [ -L "$target" ]; then
    rm -rf -- "$target"
    echo "Removed $target"
  else
    echo "Skipped $target (missing)"
  fi
done

echo
echo "Run composer install to restore dependencies."
