#!/bin/bash
# Script per rimuovere completamente il vecchio plugin WECOOP CRM dal database

echo "🧹 Pulizia database da vecchio plugin WECOOP CRM..."

# SQL per rimuovere il plugin dalla lista active_plugins
mysql -u root -p << 'EOF'
USE wordpress_wecoop;

-- Rimuovi plugin dalla lista active_plugins
UPDATE wp_options 
SET option_value = REPLACE(option_value, 's:50:"wecoop-soci/wecoop-crm.php";', '') 
WHERE option_name = 'active_plugins';

-- Rimuovi eventuali opzioni del vecchio plugin
DELETE FROM wp_options WHERE option_name LIKE 'wecoop_crm%';

-- Mostra plugin attivi
SELECT option_value FROM wp_options WHERE option_name = 'active_plugins';
EOF

echo "✅ Pulizia completata!"
