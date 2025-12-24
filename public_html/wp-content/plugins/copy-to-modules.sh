#!/bin/bash
# Script per copiare i file dal plugin wecoop-soci pulito ai plugin modulari
# Autore: WeCoop Team
# Data: 23 dicembre 2025

set -e  # Esce in caso di errore

PLUGINS_DIR="/Users/aynix/Documents/Wordpress/WeCoop/www.wecoop.org/wp-content/plugins"
SOURCE_DIR="$PLUGINS_DIR/wecoop-soci"

echo "🚀 Inizio copia file ai plugin modulari..."
echo "=========================================="

# Verifica che la directory sorgente esista
if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ Errore: Directory $SOURCE_DIR non trovata"
    exit 1
fi

# ============================================
# 1. WECOOP-SOCI (già pulito, solo creazione struttura)
# ============================================
echo ""
echo "📦 1/8 - WECOOP-SOCI (già presente)..."
echo "✅ wecoop-soci già pulito e pronto"

# ============================================
# 2. WECOOP-NOTIFICATIONS
# ============================================
echo ""
echo "📦 2/8 - Copia file a WECOOP-NOTIFICATIONS..."
DEST="$PLUGINS_DIR/wecoop-notifications"

# Cerca i file nelle cartelle originali non ancora migrate
if [ -d "$PLUGINS_DIR/wecoop-crm/includes" ]; then
    OLD_SOURCE="$PLUGINS_DIR/wecoop-crm/includes"
elif [ -d "$SOURCE_DIR/../wecoop-crm/includes" ]; then
    OLD_SOURCE="$SOURCE_DIR/../wecoop-crm/includes"
else
    OLD_SOURCE=""
fi

mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/push"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/api/class-push-endpoint.php" ] && cp -f "$OLD_SOURCE/api/class-push-endpoint.php" "$DEST/includes/api/" && echo "  ✓ class-push-endpoint.php"
    [ -f "$OLD_SOURCE/push/class-push-integrations.php" ] && cp -f "$OLD_SOURCE/push/class-push-integrations.php" "$DEST/includes/push/" && echo "  ✓ class-push-integrations.php"
    [ -f "$OLD_SOURCE/admin/class-push-notifications-admin.php" ] && cp -f "$OLD_SOURCE/admin/class-push-notifications-admin.php" "$DEST/includes/admin/" && echo "  ✓ class-push-notifications-admin.php"
fi
echo "✅ wecoop-notifications completato"

# ============================================
# 3. WECOOP-EVENTI
# ============================================
echo ""
echo "📦 3/8 - Copia file a WECOOP-EVENTI..."
DEST="$PLUGINS_DIR/wecoop-eventi"
mkdir -p "$DEST/includes/post-types"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/post-types/class-evento.php" ] && cp -f "$OLD_SOURCE/post-types/class-evento.php" "$DEST/includes/post-types/" && echo "  ✓ class-evento.php"
    [ -f "$OLD_SOURCE/api/class-eventi-endpoint.php" ] && cp -f "$OLD_SOURCE/api/class-eventi-endpoint.php" "$DEST/includes/api/" && echo "  ✓ class-eventi-endpoint.php"
    [ -f "$OLD_SOURCE/admin/class-eventi-admin.php" ] && cp -f "$OLD_SOURCE/admin/class-eventi-admin.php" "$DEST/includes/admin/" && echo "  ✓ class-eventi-admin.php"
fi
echo "✅ wecoop-eventi completato"

# ============================================
# 4. WECOOP-SERVIZI
# ============================================
echo ""
echo "📦 4/8 - Copia file a WECOOP-SERVIZI..."
DEST="$PLUGINS_DIR/wecoop-servizi"
mkdir -p "$DEST/includes/post-types"
mkdir -p "$DEST/includes/api"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/post-types/class-richiesta-servizio.php" ] && cp -f "$OLD_SOURCE/post-types/class-richiesta-servizio.php" "$DEST/includes/post-types/" && echo "  ✓ class-richiesta-servizio.php"
    [ -f "$OLD_SOURCE/api/class-servizi-endpoint.php" ] && cp -f "$OLD_SOURCE/api/class-servizi-endpoint.php" "$DEST/includes/api/" && echo "  ✓ class-servizi-endpoint.php"
fi
echo "✅ wecoop-servizi completato"

# ============================================
# 5. WECOOP-LEADS
# ============================================
echo ""
echo "📦 5/8 - Copia file a WECOOP-LEADS..."
DEST="$PLUGINS_DIR/wecoop-leads"
mkdir -p "$DEST/includes/crm"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/crm/class-lead-cpt.php" ] && cp -f "$OLD_SOURCE/crm/class-lead-cpt.php" "$DEST/includes/crm/" && echo "  ✓ class-lead-cpt.php"
    [ -f "$OLD_SOURCE/crm/class-pipeline-manager.php" ] && cp -f "$OLD_SOURCE/crm/class-pipeline-manager.php" "$DEST/includes/crm/" && echo "  ✓ class-pipeline-manager.php"
    [ -f "$OLD_SOURCE/crm/class-goals-reports.php" ] && cp -f "$OLD_SOURCE/crm/class-goals-reports.php" "$DEST/includes/crm/" && echo "  ✓ class-goals-reports.php"
    [ -f "$OLD_SOURCE/crm/class-import-export.php" ] && cp -f "$OLD_SOURCE/crm/class-import-export.php" "$DEST/includes/crm/" && echo "  ✓ class-import-export.php"
    [ -f "$OLD_SOURCE/api/class-lead-endpoint.php" ] && cp -f "$OLD_SOURCE/api/class-lead-endpoint.php" "$DEST/includes/api/" && echo "  ✓ class-lead-endpoint.php"
    [ -f "$OLD_SOURCE/admin/class-crm-menu.php" ] && cp -f "$OLD_SOURCE/admin/class-crm-menu.php" "$DEST/includes/admin/" && echo "  ✓ class-crm-menu.php"
fi
echo "✅ wecoop-leads completato"

# ============================================
# 6. WECOOP-EMAIL-SYSTEM
# ============================================
echo ""
echo "📦 6/8 - Copia file a WECOOP-EMAIL-SYSTEM..."
DEST="$PLUGINS_DIR/wecoop-email-system"
mkdir -p "$DEST/includes/emails"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/emails/class-email-i18n.php" ] && cp -f "$OLD_SOURCE/emails/class-email-i18n.php" "$DEST/includes/emails/" && echo "  ✓ class-email-i18n.php"
    [ -f "$OLD_SOURCE/emails/class-email-template.php" ] && cp -f "$OLD_SOURCE/emails/class-email-template.php" "$DEST/includes/emails/" && echo "  ✓ class-email-template.php"
    [ -f "$OLD_SOURCE/emails/class-email-manager.php" ] && cp -f "$OLD_SOURCE/emails/class-email-manager.php" "$DEST/includes/emails/" && echo "  ✓ class-email-manager.php"
    [ -f "$OLD_SOURCE/emails/class-email-tracker.php" ] && cp -f "$OLD_SOURCE/emails/class-email-tracker.php" "$DEST/includes/emails/" && echo "  ✓ class-email-tracker.php"
fi
echo "✅ wecoop-email-system completato"

# ============================================
# 7. WECOOP-WHATSAPP
# ============================================
echo ""
echo "📦 7/8 - Copia file a WECOOP-WHATSAPP..."
DEST="$PLUGINS_DIR/wecoop-whatsapp"
mkdir -p "$DEST/includes/whatsapp"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/whatsapp/class-whatsapp.php" ] && cp -f "$OLD_SOURCE/whatsapp/class-whatsapp.php" "$DEST/includes/whatsapp/" && echo "  ✓ class-whatsapp.php"
fi
echo "✅ wecoop-whatsapp completato"

# ============================================
# 8. WECOOP-AUTOMATION
# ============================================
echo ""
echo "📦 8/8 - Copia file a WECOOP-AUTOMATION..."
DEST="$PLUGINS_DIR/wecoop-automation"
mkdir -p "$DEST/includes/automation"

if [ -n "$OLD_SOURCE" ]; then
    [ -f "$OLD_SOURCE/automation/class-automation.php" ] && cp -f "$OLD_SOURCE/automation/class-automation.php" "$DEST/includes/automation/" && echo "  ✓ class-automation.php"
fi
echo "✅ wecoop-automation completato"

# ============================================
# VERIFICA FILE COPIATI
# ============================================
echo ""
echo "=========================================="
echo "🔍 Verifica file copiati..."
echo "=========================================="
echo ""

# Funzione per contare file PHP
count_php_files() {
    local dir=$1
    if [ -d "$dir/includes" ]; then
        find "$dir/includes" -name "*.php" | wc -l | tr -d ' '
    else
        echo "0"
    fi
}

echo "Plugin creati e file copiati:"
echo "  • wecoop-core: già esistente"
echo "  • wecoop-soci: $(count_php_files "$PLUGINS_DIR/wecoop-soci") file"
echo "  • wecoop-notifications: $(count_php_files "$PLUGINS_DIR/wecoop-notifications") file"
echo "  • wecoop-eventi: $(count_php_files "$PLUGINS_DIR/wecoop-eventi") file"
echo "  • wecoop-servizi: $(count_php_files "$PLUGINS_DIR/wecoop-servizi") file"
echo "  • wecoop-leads: $(count_php_files "$PLUGINS_DIR/wecoop-leads") file"
echo "  • wecoop-email-system: $(count_php_files "$PLUGINS_DIR/wecoop-email-system") file"
echo "  • wecoop-whatsapp: $(count_php_files "$PLUGINS_DIR/wecoop-whatsapp") file"
echo "  • wecoop-automation: $(count_php_files "$PLUGINS_DIR/wecoop-automation") file"

# ============================================
# RIEPILOGO
# ============================================
echo ""
echo "=========================================="
echo "✅ Migrazione completata!"
echo "=========================================="
echo ""
echo "🔧 Prossimi passi:"
echo "   1. Vai su WordPress Admin → Plugin"
echo "   2. Disattiva 'WECOOP CRM Completo' (se ancora attivo)"
echo "   3. Attiva i plugin nell'ordine:"
echo "      a. WeCoop Core (OBBLIGATORIO - base per tutti)"
echo "      b. WeCoop Soci"
echo "      c. WeCoop Notifications"
echo "      d. WeCoop Eventi"
echo "      e. WeCoop Servizi"
echo "      f. WeCoop Leads"
echo "      g. WeCoop Email System"
echo "      h. WeCoop WhatsApp (opzionale)"
echo "      i. WeCoop Automation (opzionale)"
echo ""
echo "⚠️  IMPORTANTE:"
echo "   - Tutti i plugin dipendono da WeCoop Core"
echo "   - Attiva sempre WeCoop Core per primo"
echo "   - Dopo l'attivazione, vai su Impostazioni → Permalink"
echo "     e clicca 'Salva modifiche' per aggiornare le rotte"
echo ""
