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

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"
}

log "=== Iniciando deploy de tickets-dec ==="

if [ ! -d "$APP_DIR/.git" ]; then
    log "ERRO: $APP_DIR não é um repositório git."
    exit 1
fi

cd "$APP_DIR"

# 1. Permissões base (garante que www-data tenha acesso)
log "Ajustando permissões..."
install -d -o www-data -g www-data storage storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2. Atualizar o código
log "Atualizando código (git pull)..."
if [ -n "$(git status --porcelain)" ]; then
    log "Há alterações locais. Fazendo stash..."
    git stash push -m "auto-deploy-$(date +%s)"
    STASHED=1
else
    STASHED=0
fi

git pull --rebase --ff-only

if [ "$STASHED" = "1" ]; then
    log "Restaurando alterações locais (stash pop)..."
    git stash pop || log "AVISO: conflitos no stash pop - revise manualmente."
fi

# 3. Dependências PHP
log "Instalando dependências Composer..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 4. Dependências JS e build
log "Instalando dependências npm..."
"$NPM_BIN" install --no-audit --no-fund
log "Compilando assets..."
"$NPM_BIN" run build

# 5. Migrações
log "Rodando migrações..."
"$PHP_BIN" artisan migrate --force

# 6. Storage link
log "Garantindo storage:link..."
"$PHP_BIN" artisan storage:link || true

# 7. Caches
log "Limpando e regerando caches..."
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache

# 8. Reiniciar a fila (supervisor)
log "Reiniciando fila ($QUEUE_SUPERVISOR_NAME)..."
if command -v supervisorctl >/dev/null 2>&1; then
    supervisorctl restart "$QUEUE_SUPERVISOR_NAME:*" || log "AVISO: não foi possível reiniciar a fila."
else
    log "AVISO: supervisorctl não encontrado - reinicie a fila manualmente."
fi

# 9. Recarregar PHP-FPM
log "Recarregando PHP-FPM..."
if command -v systemctl >/dev/null 2>&1; then
    systemctl reload php8.4-fpm || systemctl reload php-fpm || log "AVISO: não foi possível recarregar o PHP-FPM."
else
    log "AVISO: systemctl não encontrado."
fi

# 10. Permissões finais
log "Ajustando permissões finais..."
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

log "=== Deploy concluído com sucesso ==="
log "Verifique o site: https://ticket.brazil.vps-kinghost.net"