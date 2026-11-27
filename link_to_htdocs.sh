#!/bin/bash
# =====================================================================
# Script to link /home/ktec/Projects/university_sims to /opt/lampp/htdocs
# =====================================================================

TARGET_PROJECT="/home/ktec/Projects/university_sims"
HTDOCS_LINK="/opt/lampp/htdocs/university_sims"

echo "=========================================================="
echo " UniSIMS: Linking Project to XAMPP htdocs"
echo "=========================================================="

if [ "$EUID" -ne 0 ]; then
    echo "This script requires sudo privileges to modify /opt/lampp/htdocs."
    echo "Running with sudo..."
    exec sudo bash "$0" "$@"
fi

if [ ! -d "/opt/lampp/htdocs" ]; then
    echo "ERROR: /opt/lampp/htdocs does not exist. Is XAMPP installed?"
    exit 1
fi

if [ -L "$HTDOCS_LINK" ]; then
    echo "Found existing symbolic link at $HTDOCS_LINK. Replacing..."
    rm -f "$HTDOCS_LINK"
elif [ -d "$HTDOCS_LINK" ]; then
    echo "Backing up old static folder to /opt/lampp/htdocs/university_sims_backup..."
    rm -rf /opt/lampp/htdocs/university_sims_backup
    mv "$HTDOCS_LINK" /opt/lampp/htdocs/university_sims_backup
fi

ln -s "$TARGET_PROJECT" "$HTDOCS_LINK"
chmod -R a+rX "$TARGET_PROJECT"

echo "SUCCESS! Linked successfully:"
echo "  $HTDOCS_LINK -> $TARGET_PROJECT"
echo ""
echo "Access the live application in your browser:"
echo "  -> http://localhost/university_sims/"
echo "  -> http://localhost/university_sims/public/"
echo "=========================================================="
