# =============================================================
#  FTP Deployment Configuration
#  Edit this file to match your server settings
# =============================================================

# --- FTP Server ---
$FTP_HOST     = "85.187.128.60"
$FTP_USER     = "xin@panlingyi.tw"
$FTP_PASS     = "Ss@0952826333"

# Remote root path on the FTP server (where your site files live)
$FTP_REMOTE_ROOT = "/"

# --- Local Source ---
# Path to the folder whose contents will be uploaded
# Defaults to the parent folder of this deploy\ directory
$LOCAL_SOURCE = (Get-Item "$PSScriptRoot\..").FullName

# --- Exclusions ---
# Files / folders to skip during upload (glob-style names)
$EXCLUDE_LIST = @(
    "deploy",         # this deployment folder itself
    ".git",
    ".gitignore",
    "node_modules",
    "*.log",
    "*.sql",
    "database",       # SQL files only needed locally
    "uploads",        # User uploads, don't overwrite remote
    "Thumbs.db",
    ".DS_Store"
)
