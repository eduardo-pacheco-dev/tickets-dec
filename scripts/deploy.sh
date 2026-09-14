#!/usr/bin/env bash
#
# Deploy script for tickets-dec (Laravel) on Debian VPS.
# Usage: sudo bash scripts/deploy.sh
#
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/tickets-dec}"
PHP_BIN="${PHP_BIN:-php}"
NPM_BIN="${NPM_BIN:-npm}"
QUEUE_SUPERVISOR_NAME="${QUEUE_SUPERVISOR_NAME:-tickets-queue}"
LOG_FILE="${LOG_FILE:-$APP_DIR/storage/logs/deploy.log}"

# ── Cores (desativadas se não for TTY) ──────────────────────────────
if [ -t 1 ]; then
    C_GREEN=$'\e[32m'; C_RED=$'\e[31m'; C_YELLOW=$'\e[33m'
    C_CYAN=$'\e[36m'; C_BOLD=$'\e[1m'; C_DIM=$'\e[2m'; C_RESET=$'\e[0m'
else
    C_GREEN=""; C_RED=""; C_YELLOW=""; C_CYAN=""; C_BOLD=""; C_DIM=""; C_RESET=""
fi

START_TIME=$(date +%s)

log() {
    echo "${C_DIM}[$(date '+%H:%M:%S')]${C_RESET} $*" | tee -a "$LOG_FILE"
}

ok()   { echo "${C_GREEN}✓${C_RESET} $*" | tee -a "$LOG_FILE"; }
warn() { echo "${C_YELLOW}!${C_RESET} $*" | tee -a "$LOG_FILE"; }
fail() { echo "${C_RED}✗${C_RESET} $*" | tee -a "$LOG_FILE"; }

step() {
    echo "" | tee -a "$LOG_FILE"
    echo "${C_BOLD}${C_CYAN}━━━ $* ━━━${C_RESET}" | tee -a "$LOG_FILE"
}

elapsed() {
    local now diff
    now=$(date +%s)
    diff=$((now - START_TIME))
    printf "%02d:%02d" $((diff / 60)) $((diff % 60))
}

# ── Início ──────────────────────────────────────────────────────────
echo "" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}════════════════════════════════════════════════${C_RESET}" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}  DEPLOY - tickets-dec${C_RESET}" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}════════════════════════════════════════════════${C_RESET}" | tee -a "$LOG_FILE"
log "Iniciado em $(date '+%Y-%m-%d %H:%M:%S')"

if [ ! -d "$APP_DIR/.git" ]; then
    fail "ERRO: $APP_DIR não é um repositório git."
    exit 1
fi

cd "$APP_DIR"

# ── 1. Permissões base ──────────────────────────────────────────────
step "1/9 · Permissões"
install -d -o www-data -g www-data storage storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache
ok "Permissões ajustadas ($(elapsed))"

# ── 2. Atualizar o código ───────────────────────────────────────────
step "2/9 · Código"
git fetch origin main
git reset --hard origin/main
ok "Código atualizado ($(elapsed))"

# ── 3. Dependências PHP ─────────────────────────────────────────────
step "3/9 · Composer"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
ok "Dependências PHP instaladas ($(elapsed))"

# ── 4. Dependências JS e build ──────────────────────────────────────
step "4/9 · npm + build"
"$NPM_BIN" install --no-audit --no-fund
"$NPM_BIN" run build
ok "Assets compilados ($(elapsed))"

# ── 5. Migrações ────────────────────────────────────────────────────
step "5/9 · Migrações"
"$PHP_BIN" artisan migrate --force
ok "Migrações aplicadas ($(elapsed))"

# ── 6. Storage link ─────────────────────────────────────────────────
step "6/9 · Storage link"
if "$PHP_BIN" artisan storage:link; then
    ok "Storage link ok ($(elapsed))"
else
    warn "Storage link já existia ($(elapsed))"
fi

# ── 7. Caches ───────────────────────────────────────────────────────
step "7/9 · Caches"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache
ok "Caches regenerados ($(elapsed))"

# ── 8. Reiniciar fila ───────────────────────────────────────────────
step "8/9 · Fila (supervisor)"
if command -v supervisorctl >/dev/null 2>&1; then
    if supervisorctl restart "$QUEUE_SUPERVISOR_NAME:*"; then
        ok "Fila reiniciada ($(elapsed))"
    else
        warn "Não foi possível reiniciar a fila ($(elapsed))"
    fi
else
    warn "supervisorctl não encontrado - reinicie a fila manualmente ($(elapsed))"
fi

# ── 9. PHP-FPM ──────────────────────────────────────────────────────
step "9/9 · PHP-FPM"
if command -v systemctl >/dev/null 2>&1; then
    if systemctl reload php8.4-fpm || systemctl reload php-fpm; then
        ok "PHP-FPM recarregado ($(elapsed))"
    else
        warn "Não foi possível recarregar o PHP-FPM ($(elapsed))"
    fi
else
    warn "systemctl não encontrado ($(elapsed))"
fi

# ── Permissões finais ───────────────────────────────────────────────
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ── Conclusão ───────────────────────────────────────────────────────
echo "" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}════════════════════════════════════════════════${C_RESET}" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}  ✅ DEPLOY CONCLUÍDO em $(elapsed)${C_RESET}" | tee -a "$LOG_FILE"
echo "${C_BOLD}${C_GREEN}════════════════════════════════════════════════${C_RESET}" | tee -a "$LOG_FILE"
log "Finalizado em $(date '+%Y-%m-%d %H:%M:%S')"
log "Site: https://ticket.brazil.vps-kinghost.net"