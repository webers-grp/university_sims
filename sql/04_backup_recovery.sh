#!/usr/bin/env bash
# =====================================================================
# Database Backup and Recovery Demonstration Script
# System: University Student Information Management System (university_sims)
# =====================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/../backups"
DB_NAME="university_sims"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_backup_${TIMESTAMP}.sql"

mkdir -p "${BACKUP_DIR}"

# Determine MySQL client & socket (support XAMPP, system, and TCP)
if [ -x "/opt/lampp/bin/mysql" ] && [ -S "/opt/lampp/var/mysql/mysql.sock" ]; then
    MYSQL_CMD="/opt/lampp/bin/mysql -u root -S /opt/lampp/var/mysql/mysql.sock"
    DUMP_CMD="/opt/lampp/bin/mysqldump -u root -S /opt/lampp/var/mysql/mysql.sock"
elif [ -S "/opt/lampp/var/mysql/mysql.sock" ]; then
    MYSQL_CMD="mariadb -u root -S /opt/lampp/var/mysql/mysql.sock"
    DUMP_CMD="mariadb-dump -u root -S /opt/lampp/var/mysql/mysql.sock"
elif [ -S "/tmp/mysql.sock" ]; then
    MYSQL_CMD="mariadb -u root -S /tmp/mysql.sock"
    DUMP_CMD="mariadb-dump -u root -S /tmp/mysql.sock"
else
    MYSQL_CMD="mariadb -u root -h 127.0.0.1 -P 3306"
    DUMP_CMD="mariadb-dump -u root -h 127.0.0.1 -P 3306"
fi

echo "=========================================================="
echo " 1. STARTING BACKUP: ${DB_NAME}"
echo "=========================================================="

${DUMP_CMD} \
    --triggers \
    --single-transaction \
    --databases "${DB_NAME}" > "${BACKUP_FILE}"

echo "[SUCCESS] Backup successfully created at:"
echo "   ${BACKUP_FILE}"
ls -lh "${BACKUP_FILE}"

echo ""
echo "=========================================================="
echo " 2. TESTING DISASTER RECOVERY (SIMULATED DATABASE RESTORE)"
echo "=========================================================="

RECOVERY_DB="university_sims_recovery_test"

${MYSQL_CMD} -e "DROP DATABASE IF EXISTS ${RECOVERY_DB}; CREATE DATABASE ${RECOVERY_DB};"

sed "s/\`${DB_NAME}\`/\`${RECOVERY_DB}\`/g" "${BACKUP_FILE}" | ${MYSQL_CMD} "${RECOVERY_DB}"

echo "[SUCCESS] Backup restored into '${RECOVERY_DB}' successfully!"

echo ""
echo "=========================================================="
echo " 3. VERIFYING TABLE ROW COUNTS IN RESTORED DATABASE"
echo "=========================================================="

${MYSQL_CMD} -e "
USE ${RECOVERY_DB};
SELECT 'departments' AS table_name, COUNT(*) AS row_count FROM departments
UNION ALL
SELECT 'instructors', COUNT(*) FROM instructors
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'courses', COUNT(*) FROM courses
UNION ALL
SELECT 'course_registrations', COUNT(*) FROM course_registrations
UNION ALL
SELECT 'exam_results', COUNT(*) FROM exam_results
UNION ALL
SELECT 'fee_payments', COUNT(*) FROM fee_payments;
"

${MYSQL_CMD} -e "DROP DATABASE IF EXISTS ${RECOVERY_DB};"
echo "[SUCCESS] Recovery verification complete and test database cleaned up."
