#!/bin/bash

# Script da eseguire SUL SERVER via SSH
# 
# Opzione 1 - Esegui questo comando localmente:
# ssh u703617904@185.212.184.78 -p 65002 'bash -s' < install-vendor-remote.sh
#
# Opzione 2 - Copia ed esegui direttamente sul server:
# ssh u703617904@185.212.184.78 -p 65002
# cd /home/u703617904/domains/wecoop.org/public_html/wp-content/plugins/wecoop-servizi
# bash install-vendor-remote.sh

echo "🚀 Installazione dipendenze Composer per WeCoop Servizi..."

# Vai alla directory del plugin
cd /home/u703617904/domains/wecoop.org/public_html/wp-content/plugins/wecoop-servizi

# Verifica che composer.json esista
if [ ! -f "composer.json" ]; then
    echo "❌ composer.json non trovato!"
    exit 1
fi

echo "📄 composer.json trovato"
echo ""

# Esegui composer install
echo "📦 Installazione dipendenze..."
/opt/alt/php83/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader

# Verifica che vendor/ sia stato creato
if [ -d "vendor" ]; then
    echo ""
    echo "✅ Vendor installato con successo!"
    echo ""
    echo "📊 Statistiche:"
    echo "   Dimensione: $(du -sh vendor | cut -f1)"
    echo "   File: $(find vendor -type f | wc -l)"
    echo ""
    echo "📂 Contenuto vendor/:"
    ls -lh vendor/ | head -10
    echo ""
    echo "✅ Tutto pronto!"
    echo "🧪 Testa su: https://www.wecoop.org/wp-admin/admin.php?page=wecoop-richieste-servizi"
else
    echo "❌ Errore: vendor/ non creato"
    exit 1
fi
