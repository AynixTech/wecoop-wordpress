#!/bin/bash

# Script per caricare vendor/ sul server WeCoop
# Esegui da: wp-content/plugins/wecoop-servizi/
# Uso: ./upload-vendor.sh

echo "🚀 Upload vendor/ a wecoop.org..."

# Configurazione server
SERVER="u703617904@185.212.184.78"
PORT="65002"
REMOTE_DIR="/home/u703617904/domains/wecoop.org/public_html/wp-content/plugins/wecoop-servizi"

# Verifica che vendor/ esista
if [ ! -d "vendor" ]; then
    echo "❌ Cartella vendor/ non trovata. Esegui prima: composer install"
    exit 1
fi

echo "📦 Creazione archivio vendor.tar.gz..."
tar -czf vendor.tar.gz vendor/

echo "📤 Upload archivio sul server ($(du -h vendor.tar.gz | cut -f1))..."
scp -P $PORT vendor.tar.gz $SERVER:/tmp/

echo "📂 Estrazione sul server..."
ssh -p $PORT $SERVER "cd $REMOTE_DIR && \
    rm -rf vendor && \
    tar -xzf /tmp/vendor.tar.gz && \
    rm /tmp/vendor.tar.gz && \
    echo '✅ Vendor installato con successo!' && \
    echo '' && \
    echo 'Contenuto vendor/:' && \
    ls -lh vendor/ | head -10"

# Cleanup locale
rm vendor.tar.gz

echo ""
echo "✅ Upload completato!"
echo "📍 Path remoto: $REMOTE_DIR/vendor/"
echo ""
echo "🧪 Testa ora la generazione ricevute su:"
echo "   https://www.wecoop.org/wp-admin/admin.php?page=wecoop-richieste-servizi"
