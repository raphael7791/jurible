#!/bin/bash
# ============================================
# MIGRATION — ecole.aideauxtd.com (école)
# Remplace par ecole.jurible.com
# ============================================

set -euo pipefail

BACKUP_DIR=~/backups/migration-20260320-1803
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
LOG=~/backups/migration-ecole-$TIMESTAMP.log

{
echo "============================================"
echo "  MIGRATION — ecole.aideauxtd.com"
echo "  $(date)"
echo "============================================"
echo ""

# ── STEP 1 : Export frais BDD ecole.jurible.com ───────────

echo "[$(date)] === STEP 1/11 : Export BDD ecole.jurible.com (état frais) ==="
cd ~/ecole.jurible.com
wp db export ~/backups/ecole-jurible-fresh-$TIMESTAMP.sql --quiet
echo "✓ ~/backups/ecole-jurible-fresh-$TIMESTAMP.sql"

# ── STEP 2 : Sauvegardes wp-config.php ────────────────────

echo ""
echo "[$(date)] === STEP 2/11 : Sauvegarde wp-config.php ==="
cp ~/ecole/wp-config.php ~/backups/wp-config-ecole-$TIMESTAMP.php
echo "✓ wp-config.php sauvegardé"

# ── STEP 3 : Renommer ecole ───────────────────────────────

echo ""
echo "[$(date)] === STEP 3/11 : Renommer ~/ecole → ~/ecole_old ==="
if [ -d ~/ecole_old ]; then
    echo "⚠ ~/ecole_old existe déjà — suppression"
    rm -rf ~/ecole_old
fi
mv ~/ecole ~/ecole_old
echo "✓ Ancien site déplacé"

# ── STEP 4 : Copier ecole.jurible.com → ecole ─────────────

echo ""
echo "[$(date)] === STEP 4/11 : Copier ~/ecole.jurible.com → ~/ecole ==="
cp -a ~/ecole.jurible.com ~/ecole
echo "✓ Fichiers ecole.jurible.com copiés"

# ── STEP 5 : Fusionner anciens uploads ────────────────────

echo ""
echo "[$(date)] === STEP 5/11 : Fusionner anciens uploads ==="
if [ -d ~/ecole_old/wp-content/uploads ]; then
    mkdir -p ~/ecole/wp-content/uploads
    cp -rn ~/ecole_old/wp-content/uploads/* ~/ecole/wp-content/uploads/ 2>/dev/null || true
    echo "✓ Anciens uploads fusionnés (sans écraser les nouveaux)"
else
    echo "⚠ Pas d'anciens uploads trouvés"
fi

# ── STEP 6 : wp-config.php fusionné ───────────────────────

echo ""
echo "[$(date)] === STEP 6/11 : wp-config.php fusionné ==="

# On part du wp-config.php d'ecole.aideauxtd (credentials BDD correctes)
cp ~/backups/wp-config-ecole-$TIMESTAMP.php ~/ecole/wp-config.php

# Changer le préfixe
sed -i "s/\$table_prefix = 'wpk9_';/\$table_prefix = 'wpi3_';/" ~/ecole/wp-config.php
echo "  - Préfixe → wpi3_"

# Ajouter SURECART_ENCRYPTION_KEY (celle d'ecole.jurible.com)
if ! grep -q 'SURECART_ENCRYPTION_KEY' ~/ecole/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define( 'SURECART_ENCRYPTION_KEY', 's0rwrrgiqpwcutmhn6bomssug5xkmhunoxr39zjoigkwteebbwqskmo6fvft7kz8' );" ~/ecole/wp-config.php
    echo "  - SURECART_ENCRYPTION_KEY ajoutée"
fi

# Ajouter optimisations
if ! grep -q 'DISABLE_WP_CRON' ~/ecole/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('DISABLE_WP_CRON', true);" ~/ecole/wp-config.php
    echo "  - DISABLE_WP_CRON ajouté"
fi
if ! grep -q 'WP_POST_REVISIONS' ~/ecole/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('WP_POST_REVISIONS', 3);" ~/ecole/wp-config.php
    echo "  - WP_POST_REVISIONS ajouté"
fi
if ! grep -q 'EMPTY_TRASH_DAYS' ~/ecole/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('EMPTY_TRASH_DAYS', 7);" ~/ecole/wp-config.php
    echo "  - EMPTY_TRASH_DAYS ajouté"
fi

# Retirer WP_DEVELOPMENT_MODE si présent
sed -i "/WP_DEVELOPMENT_MODE/d" ~/ecole/wp-config.php

# Forcer WP_DEBUG à false (sécurité anti-ban PowerBoost)
sed -i "s/define('WP_DEBUG', true);/define('WP_DEBUG', false);/" ~/ecole/wp-config.php
sed -i "s/define('WP_DEBUG_LOG', true);/define('WP_DEBUG_LOG', false);/" ~/ecole/wp-config.php

echo "✓ wp-config.php prêt"

# ── STEP 7 : Reset BDD + Import ───────────────────────────

echo ""
echo "[$(date)] === STEP 7/11 : Reset BDD + Import ==="
cd ~/ecole
wp db reset --yes --quiet
echo "  - BDD vidée"
wp db import ~/backups/ecole-jurible-fresh-$TIMESTAMP.sql --quiet
echo "✓ BDD ecole.jurible.com importée (inclut users + FluentCRM)"

# ── STEP 8 : Search-replace domaine ───────────────────────

echo ""
echo "[$(date)] === STEP 8/11 : Search-replace domaine ==="
wp search-replace 'https://ecole.jurible.com' 'https://ecole.aideauxtd.com' --all-tables --precise --quiet
wp search-replace 'http://ecole.jurible.com' 'https://ecole.aideauxtd.com' --all-tables --precise --quiet
wp search-replace 'ecole.jurible.com' 'ecole.aideauxtd.com' --all-tables --precise --quiet
echo "✓ ecole.jurible.com → ecole.aideauxtd.com"

# ── STEP 9 : Search-replace chemins fichiers ──────────────

echo ""
echo "[$(date)] === STEP 9/11 : Search-replace chemins ==="
wp search-replace '/home/aideauxtd/ecole.jurible.com' '/home/aideauxtd/ecole' --all-tables --precise --quiet
echo "✓ Chemins fichiers mis à jour"

# ── STEP 10 : Fluent SMTP ─────────────────────────────────

echo ""
echo "[$(date)] === STEP 10/11 : Fluent SMTP ==="
# Importer la même config que l'ancien site
wp eval "
\$file = '$BACKUP_DIR/fluentsmtp-options.txt';
if (file_exists(\$file)) {
    \$lines = file(\$file, FILE_IGNORE_NEW_LINES);
    if (count(\$lines) >= 2) {
        \$parts = explode(\"\\t\", \$lines[1]);
        if (count(\$parts) >= 3) {
            update_option(\$parts[1], maybe_unserialize(\$parts[2]));
            echo 'Fluent SMTP importé';
        }
    }
} else {
    echo 'Fichier SMTP non trouvé — configurer manuellement';
}
"
echo ""
echo "✓ Fluent SMTP (vérifier dans wp-admin si OK)"

# ── STEP 11 : Cache flush ─────────────────────────────────

echo ""
echo "[$(date)] === STEP 11/11 : Cache flush ==="
wp cache flush --quiet 2>/dev/null || true
wp rewrite flush --quiet 2>/dev/null || true
echo "✓ Cache et permaliens vidés"

# ── RÉSUMÉ ─────────────────────────────────────────────────

echo ""
echo "============================================"
echo "  MIGRATION TERMINÉE — ecole.aideauxtd.com"
echo "  $(date)"
echo "============================================"
echo ""
echo "VÉRIFICATIONS MANUELLES :"
echo "  1. https://ecole.aideauxtd.com → homepage"
echo "  2. https://ecole.aideauxtd.com/wp-admin → se connecter"
echo "  3. Se connecter avec un vrai compte client"
echo "  4. Vérifier cours Fluent Community"
echo "  5. Vérifier SureCart (re-auth si besoin)"
echo "  6. Installer FluentCRM Pro plugin (zip licence)"
echo "  7. Vérifier contacts FluentCRM"
echo "  8. Installer Fluent SMTP plugin si absent"
echo "  9. Envoyer un email test"
echo " 10. Access Manager : vérifier les règles + tester"
echo " 11. Configurer cron serveur (DISABLE_WP_CRON est actif)"
echo ""
echo "NETTOYAGE (plus tard) :"
echo "  rm -rf ~/ecole_old    # ancien site"
echo "  rm ~/backups/ecole-jurible-fresh-$TIMESTAMP.sql"
echo ""
echo "Log: $LOG"
} 2>&1 | tee -a "$LOG"
