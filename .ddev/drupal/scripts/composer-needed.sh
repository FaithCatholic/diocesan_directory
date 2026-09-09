#!/bin/bash
#ddev-generated
set -e -o pipefail

composer_root="${1:-}"
config_dir="${2:-config}"

composer_root="${composer_root%/}"
if [ "$composer_root" = "." ]; then
  composer_root=""
fi

if [ -n "$composer_root" ]; then
  composer_file="$composer_root/composer.json"
  config_path="$composer_root/$config_dir"
else
  composer_file="composer.json"
  config_path="$config_dir"
fi

if [ ! -f "$composer_file" ]; then
  echo "Missing composer file: $composer_file" >&2
  exit 1
fi

if [ ! -d "$config_path" ]; then
  echo "Missing config directory: $config_path" >&2
  exit 1
fi

active_require_modules=()
active_dev_modules=()
unused_require_modules=()
unused_dev_modules=()

enabled_modules_file="$(mktemp)"
trap 'rm -f "$enabled_modules_file"' EXIT

extract_extension_sections() {
  awk '
    /^(module|theme):/ { in_section = 1; next }
    in_section && /^[^ ]/ { in_section = 0 }
    in_section && /^  [a-zA-Z0-9_]+:/ {
      line = $0
      sub(/^  /, "", line)
      sub(/:.*$/, "", line)
      print line
    }
  ' "$1"
}

while IFS= read -r -d '' extension_file; do
  extract_extension_sections "$extension_file" >> "$enabled_modules_file"
done < <(find "$config_path" -type f \( -name 'core.extension.yml' -o -name 'config_split.config_split.*.yml' \) -print0)

sort -u -o "$enabled_modules_file" "$enabled_modules_file"

# Install base dirs come straight from composer.json's installer-paths, so we
# make no assumption about web/, docroot/, contrib/ vs custom/, etc.
install_base_dirs=()
while IFS= read -r base; do
  [ -z "$base" ] && continue
  if [ -n "$composer_root" ]; then
    install_base_dirs+=("$composer_root/$base")
  else
    install_base_dirs+=("$base")
  fi
done < <(
  php -r '
$composer_file = $argv[1];
$composer = json_decode(file_get_contents($composer_file), TRUE);
$paths = $composer["extra"]["installer-paths"] ?? [];
$bases = [];
foreach (array_keys($paths) as $pattern) {
  $pos = strpos($pattern, "{");
  if ($pos === FALSE) {
    continue;
  }
  $base = rtrim(substr($pattern, 0, $pos), "/");
  if ($base !== "") {
    $bases[$base] = TRUE;
  }
}
foreach (array_keys($bases) as $base) {
  echo $base, PHP_EOL;
}
' "$composer_file"
)

# Resolve a composer package name to the module/theme machine names it ships.
# A package directory can hold many submodules (e.g. domain, domain_extras) or
# a single module whose machine name differs from the package name (e.g. etm ->
# enhanced_taxonomy_manager), so we read every *.info.yml it installs. Test
# fixtures under tests/ are excluded. Falls back to the package name when the
# package is not installed locally or ships no info.yml.
package_machine_names() {
  local module="$1"
  local pkg_dir=""
  local base

  for base in "${install_base_dirs[@]}"; do
    if [ -d "$base/$module" ]; then
      pkg_dir="$base/$module"
      break
    fi
  done

  if [ -z "$pkg_dir" ]; then
    echo "$module"
    return
  fi

  local found=0
  local info_file
  while IFS= read -r -d '' info_file; do
    basename "$info_file" .info.yml
    found=1
  done < <(find "$pkg_dir" -name '*.info.yml' -not -path '*/tests/*' -print0)

  if [ "$found" -eq 0 ]; then
    echo "$module"
  fi
}

while IFS=: read -r dependency_type module; do
  enabled_matches=()
  while IFS= read -r machine_name; do
    [ -z "$machine_name" ] && continue
    if grep -Fxq -- "$machine_name" "$enabled_modules_file"; then
      enabled_matches+=("$machine_name")
    fi
  done < <(package_machine_names "$module")

  if [ "${#enabled_matches[@]}" -gt 0 ]; then
    # Annotate when the enabled module name(s) differ from the package name,
    # e.g. etm (enhanced_taxonomy_manager) or domain_extras (domain_sso, ...).
    label="$module"
    if ! printf '%s\n' "${enabled_matches[@]}" | grep -Fxq -- "$module"; then
      matched_list="$(printf '%s, ' "${enabled_matches[@]}")"
      matched_list="${matched_list%, }"
      label="$module ($matched_list)"
    fi
    if [ "$dependency_type" = "require-dev" ]; then
      active_dev_modules+=("$label")
    else
      active_require_modules+=("$label")
    fi
  else
    if [ "$dependency_type" = "require-dev" ]; then
      unused_dev_modules+=("$module")
    else
      unused_require_modules+=("$module")
    fi
  fi
done < <(
  php -r '
$composer_file = $argv[1];
$composer = json_decode(file_get_contents($composer_file), TRUE);
if (!is_array($composer)) {
  fwrite(STDERR, "Unable to parse composer JSON: $composer_file" . PHP_EOL);
  exit(1);
}
foreach (["require", "require-dev"] as $section) {
  foreach (array_keys($composer[$section] ?? []) as $package) {
    if (str_starts_with($package, "drupal/")) {
      $module = substr($package, strlen("drupal/"));
      if (!str_starts_with($module, "core")) {
        echo $section, ":", $module, PHP_EOL;
      }
    }
  }
}
' "$composer_file"
)

print_group() {
  local title="$1"
  local icon="$2"
  shift 2

  echo "$title"
  if [ "$#" -eq 0 ]; then
    echo "None"
    return
  fi

  for module in "$@"; do
    echo "$icon $module"
  done
}

print_group "Being used (require):" "✅" "${active_require_modules[@]}"
echo
print_group "Being used (require-dev):" "✅" "${active_dev_modules[@]}"
echo
print_group "Not being used (require):" "❌" "${unused_require_modules[@]}"
echo
print_group "Not being used (require-dev):" "❌" "${unused_dev_modules[@]}"

if [ "${#unused_require_modules[@]}" -gt 0 ] || [ "${#unused_dev_modules[@]}" -gt 0 ]; then
  echo
  echo "⚠️ Review carefully before running. Config grep can miss runtime-only dependencies."
  if [ "${#unused_require_modules[@]}" -gt 0 ]; then
    composer_remove_packages=()
    for module in "${unused_require_modules[@]}"; do
      composer_remove_packages+=("drupal/$module")
    done
    echo "🧹 composer remove ${composer_remove_packages[*]}"
  fi
  if [ "${#unused_dev_modules[@]}" -gt 0 ]; then
    composer_remove_dev_packages=()
    for module in "${unused_dev_modules[@]}"; do
      composer_remove_dev_packages+=("drupal/$module")
    done
    echo "🧹 composer remove --dev ${composer_remove_dev_packages[*]}"
  fi
fi
