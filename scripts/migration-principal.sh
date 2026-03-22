#!/bin/bash
# ============================================
# MIGRATION — aideauxtd.com (site principal)
# Remplace par jurible.com
# ============================================

set -euo pipefail

BACKUP_DIR=~/backups/migration-20260320-1803
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
LOG=~/backups/migration-principal-$TIMESTAMP.log

{
echo "============================================"
echo "  MIGRATION — aideauxtd.com"
echo "  $(date)"
echo "============================================"
echo ""

# ── STEP 1 : Export frais BDD jurible.com ─────────────────

echo "[$(date)] === STEP 1/11 : Export BDD jurible.com (état frais) ==="
cd ~/jurible.com
wp db export ~/backups/jurible-fresh-$TIMESTAMP.sql --quiet
echo "✓ ~/backups/jurible-fresh-$TIMESTAMP.sql"

# ── STEP 2 : Sauvegardes wp-config.php ────────────────────

echo ""
echo "[$(date)] === STEP 2/11 : Sauvegarde wp-config.php ==="
cp ~/public_html/wp-config.php ~/backups/wp-config-aideauxtd-$TIMESTAMP.php
echo "✓ wp-config.php sauvegardé"

# ── STEP 3 : Renommer public_html ─────────────────────────

echo ""
echo "[$(date)] === STEP 3/11 : Renommer ~/public_html → ~/public_html_old ==="
if [ -d ~/public_html_old ]; then
    echo "⚠ ~/public_html_old existe déjà — suppression"
    rm -rf ~/public_html_old
fi
mv ~/public_html ~/public_html_old
echo "✓ Ancien site déplacé"

# ── STEP 4 : Copier jurible.com → public_html ─────────────

echo ""
echo "[$(date)] === STEP 4/11 : Copier ~/jurible.com → ~/public_html ==="
cp -a ~/jurible.com ~/public_html
echo "✓ Fichiers jurible.com copiés"

# ── STEP 5 : Fusionner anciens uploads ────────────────────

echo ""
echo "[$(date)] === STEP 5/11 : Fusionner anciens uploads ==="
if [ -d ~/public_html_old/wp-content/uploads ]; then
    mkdir -p ~/public_html/wp-content/uploads
    cp -rn ~/public_html_old/wp-content/uploads/* ~/public_html/wp-content/uploads/ 2>/dev/null || true
    echo "✓ Anciens uploads fusionnés (sans écraser les nouveaux)"
else
    echo "⚠ Pas d'anciens uploads trouvés"
fi

# ── STEP 6 : wp-config.php fusionné ───────────────────────

echo ""
echo "[$(date)] === STEP 6/11 : wp-config.php fusionné ==="

# On part du wp-config.php d'aideauxtd (credentials BDD correctes + constantes)
cp ~/backups/wp-config-aideauxtd-$TIMESTAMP.php ~/public_html/wp-config.php

# Changer le préfixe
sed -i "s/\$table_prefix = 'rleu2w0c_';/\$table_prefix = 'wpyj_';/" ~/public_html/wp-config.php
echo "  - Préfixe → wpyj_"

# Ajouter SURECART_ENCRYPTION_KEY
if ! grep -q 'SURECART_ENCRYPTION_KEY' ~/public_html/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define( 'SURECART_ENCRYPTION_KEY', 'c5p0fpewnijb0ealvs7jcq778dsr6reoxmqxzswtzvq8vodbhz3npol2ixgrd0dm' );" ~/public_html/wp-config.php
    echo "  - SURECART_ENCRYPTION_KEY ajoutée"
fi

# Ajouter optimisations
if ! grep -q 'DISABLE_WP_CRON' ~/public_html/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('DISABLE_WP_CRON', true);" ~/public_html/wp-config.php
    echo "  - DISABLE_WP_CRON ajouté"
fi
if ! grep -q 'WP_POST_REVISIONS' ~/public_html/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('WP_POST_REVISIONS', 3);" ~/public_html/wp-config.php
    echo "  - WP_POST_REVISIONS ajouté"
fi
if ! grep -q 'EMPTY_TRASH_DAYS' ~/public_html/wp-config.php; then
    sed -i "/require_once.*wp-settings/i define('EMPTY_TRASH_DAYS', 7);" ~/public_html/wp-config.php
    echo "  - EMPTY_TRASH_DAYS ajouté"
fi

# Retirer WP_DEVELOPMENT_MODE si présent
sed -i "/WP_DEVELOPMENT_MODE/d" ~/public_html/wp-config.php

echo "✓ wp-config.php prêt"

# ── STEP 7 : Reset BDD + Import ───────────────────────────

echo ""
echo "[$(date)] === STEP 7/11 : Reset BDD + Import ==="
cd ~/public_html
wp db reset --yes --quiet
echo "  - BDD vidée"
wp db import ~/backups/jurible-fresh-$TIMESTAMP.sql --quiet
echo "✓ BDD jurible.com importée"

# ── STEP 8 : Search-replace domaine ───────────────────────

echo ""
echo "[$(date)] === STEP 8/11 : Search-replace domaine ==="
wp search-replace 'https://jurible.com' 'https://aideauxtd.com' --all-tables --precise --quiet
wp search-replace 'http://jurible.com' 'https://aideauxtd.com' --all-tables --precise --quiet
wp search-replace 'jurible.com' 'aideauxtd.com' --all-tables --precise --quiet
echo "✓ jurible.com → aideauxtd.com"

# ── STEP 9 : Search-replace chemins fichiers ──────────────

echo ""
echo "[$(date)] === STEP 9/11 : Search-replace chemins ==="
wp search-replace '/home/aideauxtd/jurible.com' '/home/aideauxtd/public_html' --all-tables --precise --quiet
echo "✓ Chemins fichiers mis à jour"

# ── STEP 10 : Fluent SMTP ─────────────────────────────────

echo ""
echo "[$(date)] === STEP 10/11 : Fluent SMTP ==="
# Importer via wp eval (évite les problèmes d'échappement shell)
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
echo "  MIGRATION TERMINÉE — aideauxtd.com"
echo "  $(date)"
echo "============================================"
echo ""
echo "VÉRIFICATIONS MANUELLES :"
echo "  1. https://aideauxtd.com → homepage"
echo "  2. https://aideauxtd.com/wp-admin → se connecter"
echo "  3. Vérifier SureCart (re-auth si besoin)"
echo "  4. Installer Fluent SMTP plugin si absent"
echo "  5. Envoyer un email test via Fluent SMTP"
echo "  6. Configurer cron serveur (DISABLE_WP_CRON est actif)"
echo ""
echo "NETTOYAGE (plus tard) :"
echo "  rm -rf ~/public_html_old    # ancien site"
echo "  rm ~/backups/jurible-fresh-$TIMESTAMP.sql"
echo ""
echo "Log: $LOG"
} 2>&1 | tee -a "$LOG"
