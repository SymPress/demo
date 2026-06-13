#!/bin/bash
set -euo pipefail

cache_dir="/var/cache/nginx/wordpress"

mkdir -p "${cache_dir}"
rm -f "${cache_dir}/.sympress-nginx-cache-root"
chmod 0775 "${cache_dir}" 2>/dev/null || true
