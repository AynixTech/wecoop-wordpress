#!/bin/bash
# Script per migrare i file dal plugin monolitico wecoop-soci ai plugin modulari
# Autore: WeCoop Team
# Data: 23 dicembre 2025

set -e  # Esce in caso di errore

PLUGINS_DIR="/Users/aynix/Documents/Wordpress/WeCoop/www.wecoop.org/wp-content/plugins"
SOURCE_DIR="$PLUGINS_DIR/wecoop-soci"

echo "🚀 Inizio migrazione plugin WECOOP CRM..."
echo "=========================================="

# Verifica che la directory sorgente esista
if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ Errore: Directory $SOURCE_DIR non trovata"
    exit 1
fi

# ============================================
# 1. WECOOP-SOCI
# ============================================
echo ""
echo "📦 1/8 - Migrazione WECOOP-SOCI..."
DEST="$PLUGINS_DIR/wecoop-soci"
mkdir -p "$DEST/includes/post-types"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/templates"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

cp -f "$SOURCE_DIR/includes/post-types/class-richiesta-socio.php" "$DEST/includes/post-types/" 2>/dev/null || echo "⚠️  class-richiesta-socio.php non trovato"
cp -f "$SOURCE_DIR/includes/api/class-soci-endpoint.php" "$DEST/includes/api/" 2>/dev/null || echo "⚠️  class-soci-endpoint.php non trovato"
cp -f "$SOURCE_DIR/includes/admin/class-soci-management.php" "$DEST/includes/admin/" 2>/dev/null || echo "⚠️  class-soci-management.php non trovato"
cp -f "$SOURCE_DIR/includes/class-tessera-handler.php" "$DEST/includes/" 2>/dev/null || echo "⚠️  class-tessera-handler.php non trovato"
cp -rf "$SOURCE_DIR/templates/"* "$DEST/templates/" 2>/dev/null || echo "⚠️  templates/ non trovato"
echo "✅ wecoop-soci completato"

# ============================================
# 2. WECOOP-NOTIFICATIONS
# ============================================
echo ""
echo "📦 2/8 - Migrazione WECOOP-NOTIFICATIONS..."
DEST="$PLUGINS_DIR/wecoop-notifications"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/push"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

cp -f "$SOURCE_DIR/includes/api/class-push-endpoint.php" "$DEST/includes/api/" 2>/dev/null || echo "⚠️  class-push-endpoint.php non trovato"
cp -f "$SOURCE_DIR/includes/push/class-push-integrations.php" "$DEST/includes/push/" 2>/dev/null || echo "⚠️  class-push-integrations.php non trovato"
cp -f "$SOURCE_DIR/includes/admin/class-push-notifications-admin.php" "$DEST/includes/admin/" 2>/dev/null || echo "⚠️  class-push-notifications-admin.php non trovato"
echo "✅ wecoop-notifications completato"

# ============================================
# 3. WECOOP-EVENTI
# ============================================
echo ""
echo "📦 3/8 - Migrazione WECOOP-EVENTI..."
DEST="$PLUGINS_DIR/wecoop-eventi"
mkdir -p "$DEST/includes/post-types"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

cp -f "$SOURCE_DIR/includes/post-types/class-evento.php" "$DEST/includes/post-types/" 2>/dev/null || echo "⚠️  class-evento.php non trovato"
cp -f "$SOURCE_DIR/includes/api/class-eventi-endpoint.php" "$DEST/includes/api/" 2>/dev/null || echo "⚠️  class-eventi-endpoint.php non trovato"
cp -f "$SOURCE_DIR/includes/admin/class-eventi-admin.php" "$DEST/includes/admin/" 2>/dev/null || echo "⚠️  class-eventi-admin.php non trovato"
echo "✅ wecoop-eventi completato"

# ============================================
# 4. WECOOP-SERVIZI
# ============================================
echo ""
echo "📦 4/8 - Migrazione WECOOP-SERVIZI..."
DEST="$PLUGINS_DIR/wecoop-servizi"
mkdir -p "$DEST/includes/post-types"
mkdir -p "$DEST/includes/api"

cp -f "$SOURCE_DIR/includes/post-types/class-richiesta-servizio.php" "$DEST/includes/post-types/" 2>/dev/null || echo "⚠️  class-richiesta-servizio.php non trovato"
cp -f "$SOURCE_DIR/includes/api/class-servizi-endpoint.php" "$DEST/includes/api/" 2>/dev/null || echo "⚠️  class-servizi-endpoint.php non trovato"
echo "✅ wecoop-servizi completato"

# ============================================
# 5. WECOOP-LEADS
# ============================================
echo ""
echo "📦 5/8 - Migrazione WECOOP-LEADS..."
DEST="$PLUGINS_DIR/wecoop-leads"
mkdir -p "$DEST/includes/crm"
mkdir -p "$DEST/includes/api"
mkdir -p "$DEST/includes/admin"
mkdir -p "$DEST/assets/css"
mkdir -p "$DEST/assets/js"

cp -f "$SOURCE_DIR/includes/crm/class-lead-cpt.php" "$DEST/includes/crm/" 2>/dev/null || echo "⚠️  class-lead-cpt.php non trovato"
cp -f "$SOURCE_DIR/includes/crm/class-pipeline-manager.php" "$DEST/includes/crm/" 2>/dev/null || echo "⚠️  class-pipeline-manager.php non trovato"
cp -f "$SOURCE_DIR/includes/crm/class-goals-reports.php" "$DEST/includes/crm/" 2>/dev/null || echo "⚠️  class-goals-reports.php non trovato"
cp -f "$SOURCE_DIR/includes/crm/class-import-export.php" "$DEST/includes/crm/" 2>/dev/null || echo "⚠️  class-import-export.php non trovato"
cp -f "$SOURCE_DIR/includes/api/class-lead-endpoint.php" "$DEST/includes/api/" 2>/dev/null || echo "⚠️  class-lead-endpoint.php non trovato"
cp -f "$SOURCE_DIR/includes/admin/class-crm-menu.php" "$DEST/includes/admin/" 2>/dev/null || echo "⚠️  class-crm-menu.php non trovato"
echo "✅ wecoop-leads completato"

# ============================================
# 6. WECOOP-EMAIL-SYSTEM
# ============================================
echo ""
echo "📦 6/8 - Migrazione WECOOP-EMAIL-SYSTEM..."
DEST="$PLUGINS_DIR/wecoop-email-system"
mkdir -p "$DEST/includes/emails"

cp -f "$SOURCE_DIR/includes/emails/class-email-i18n.php" "$DEST/includes/emails/" 2>/dev/null || echo "⚠️  class-email-i18n.php non trovato"
cp -f "$SOURCE_DIR/includes/emails/class-email-template.php" "$DEST/includes/emails/" 2>/dev/null || echo "⚠️  class-email-template.php non trovato"
cp -f "$SOURCE_DIR/includes/emails/class-email-manager.php" "$DEST/includes/emails/" 2>/dev/null || echo "⚠️  class-email-manager.php non trovato"
cp -f "$SOURCE_DIR/includes/emails/class-email-tracker.php" "$DEST/includes/emails/" 2>/dev/null || echo "⚠️  class-email-tracker.php non trovato"
echo "✅ wecoop-email-system completato"

# ============================================
# 7. WECOOP-WHATSAPP
# ============================================
echo ""
echo "📦 7/8 - Migrazione WECOOP-WHATSAPP..."
DEST="$PLUGINS_DIR/wecoop-whatsapp"
mkdir -p "$DEST/includes/whatsapp"

cp -f "$SOURCE_DIR/includes/whatsapp/class-whatsapp.php" "$DEST/includes/whatsapp/" 2>/dev/null || echo "⚠️  class-whatsapp.php non trovato"
echo "✅ wecoop-whatsapp completato"

# ============================================
# 8. WECOOP-AUTOMATION
# ============================================
echo ""
echo "📦 8/8 - Migrazione WECOOP-AUTOMATION..."
DEST="$PLUGINS_DIR/wecoop-automation"
mkdir -p "$DEST/includes/automation"

cp -f "$SOURCE_DIR/includes/automation/class-automation.php" "$DEST/includes/automation/" 2>/dev/null || echo "⚠️  class-automation.php non trovato"
echo "✅ wecoop-automation completato"

# ============================================
# RIEPILOGO
# ============================================
echo ""
echo "=========================================="
echo "✅ Migrazione completata!"
echo "=========================================="
echo ""
echo "📋 Plugin creati:"
echo "   1. wecoop-core (già esistente)"
echo "   2. wecoop-soci"
echo "   3. wecoop-notifications"
echo "   4. wecoop-eventi"
echo "   5. wecoop-servizi"
echo "   6. wecoop-leads"
echo "   7. wecoop-email-system"
echo "   8. wecoop-whatsapp"
echo "   9. wecoop-automation"
echo ""
echo "🔧 Prossimi passi:"
echo "   1. Vai su WordPress Admin → Plugin"
echo "   2. Disattiva 'WECOOP CRM Completo'"
echo "   3. Attiva 'WeCoop Core' (prima di tutti)"
echo "   4. Attiva gli altri plugin modulari"
echo ""
echo "⚠️  NOTA: Il vecchio plugin wecoop-crm.php in wecoop-soci/ può essere rinominato in wecoop-crm.php.old"
echo ""
