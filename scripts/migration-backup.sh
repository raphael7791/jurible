#!/bin/bash
# ============================================
# Script de backup PRE-MIGRATION — Intégral
# Sauvegarde complète (fichiers + BDD) des 4 sites
# ============================================

set -e

SSH="ssh aideauxtd@dogfish.o2switch.net"
DATE=$(date +%Y%m%d-%H%M)
BACKUP_DIR="~/backups/migration-$DATE"

echo "=== Création du dossier de backup ==="
$SSH "mkdir -p $BACKUP_DIR"

# ── BASES DE DONNÉES ──────────────────────────────────────

echo ""
echo "=== BDD 1/4 — aideauxtd.com ==="
$SSH "cd ~/public_html && wp db export $BACKUP_DIR/aideauxtd-db.sql --quiet"
echo "✓ aideauxtd-db.sql"

echo ""
echo "=== BDD 2/4 — ecole.aideauxtd.com ==="
$SSH "cd ~/ecole && wp db export $BACKUP_DIR/ecole-aideauxtd-db.sql --quiet"
echo "✓ ecole-aideauxtd-db.sql"

echo ""
echo "=== BDD 3/4 — jurible.com ==="
$SSH "cd ~/jurible.com && wp db export $BACKUP_DIR/jurible-db.sql --quiet"
echo "✓ jurible-db.sql"

echo ""
echo "=== BDD 4/4 — ecole.jurible.com ==="
$SSH "cd ~/ecole.jurible.com && wp db export $BACKUP_DIR/ecole-jurible-db.sql --quiet"
echo "✓ ecole-jurible-db.sql"

# ── FICHIERS (tar.gz compressé) ───────────────────────────

echo ""
echo "=== FICHIERS 1/4 — aideauxtd.com (49 Go, ~15-20 min) ==="
$SSH "tar czf $BACKUP_DIR/aideauxtd-files.tar.gz -C /home/aideauxtd public_html"
echo "✓ aideauxtd-files.tar.gz"

echo ""
echo "=== FICHIERS 2/4 — ecole.aideauxtd.com (13 Go, ~5-10 min) ==="
$SSH "tar czf $BACKUP_DIR/ecole-aideauxtd-files.tar.gz -C /home/aideauxtd ecole"
echo "✓ ecole-aideauxtd-files.tar.gz"

echo ""
echo "=== FICHIERS 3/4 — jurible.com (1.8 Go, ~2 min) ==="
$SSH "tar czf $BACKUP_DIR/jurible-files.tar.gz -C /home/aideauxtd jurible.com"
echo "✓ jurible-files.tar.gz"

echo ""
echo "=== FICHIERS 4/4 — ecole.jurible.com (1 Go, ~1 min) ==="
$SSH "tar czf $BACKUP_DIR/ecole-jurible-files.tar.gz -C /home/aideauxtd ecole.jurible.com"
echo "✓ ecole-jurible-files.tar.gz"

# ── EXPORTS SPÉCIFIQUES ──────────────────────────────────

echo ""
echo "=== Export FluentCRM (tables fc_*) ==="
$SSH "cd ~/public_html && DB_NAME=\$(wp config get DB_NAME) && PREFIX=\$(wp config get table_prefix) && TABLES=\$(mysql -N -e \"SHOW TABLES LIKE '\${PREFIX}fc_%'\" \$DB_NAME | tr '\n' ' ') && if [ -n \"\$TABLES\" ]; then mysqldump \$DB_NAME \$TABLES > $BACKUP_DIR/fluentcrm-export.sql && echo 'OK'; else echo 'Aucune table fc_ trouvée'; fi"
echo "✓ fluentcrm-export.sql"

echo ""
echo "=== Export config Fluent SMTP ==="
$SSH "cd ~/public_html && PREFIX=\$(wp config get table_prefix) && wp db query \"SELECT * FROM \${PREFIX}options WHERE option_name LIKE 'fluentmail%'\" > $BACKUP_DIR/fluentsmtp-options.txt"
echo "✓ fluentsmtp-options.txt"

echo ""
echo "=== Delta users ecole ==="
$SSH "cd ~/ecole && wp db query \"SELECT COUNT(*) as total FROM wpk9_users\" --quiet"
echo "(comparer avec les 7582 importés initialement)"

# ── RÉSUMÉ ────────────────────────────────────────────────

echo ""
echo "============================================"
echo "  BACKUPS TERMINÉS — $BACKUP_DIR"
echo "============================================"
echo ""
$SSH "ls -lh $BACKUP_DIR/"
echo ""
$SSH "du -sh $BACKUP_DIR/"
