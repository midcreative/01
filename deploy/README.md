# 🚀 DentFlow FTP Deployment

This folder contains PowerShell scripts for automated FTP deployment.

## 檔案說明

| 檔案 | 說明 |
|------|------|
| `ftp-config.ps1` | FTP 連線設定（主機、帳號、排除清單等） |
| `deploy.ps1` | 主要部署腳本 |
| `test-connection.ps1` | 測試 FTP 連線是否正常 |

---

## 使用方式

### 1. 先測試連線

```powershell
.\deploy\test-connection.ps1
```

### 2. 預覽（DryRun，不會真正上傳）

```powershell
.\deploy\deploy.ps1 -DryRun
```

### 3. 正式部署

```powershell
.\deploy\deploy.ps1
```

### 4. 跳過確認直接部署

```powershell
.\deploy\deploy.ps1 -Force
```

---

## 修改設定

編輯 `ftp-config.ps1` 可以調整：

- **`$FTP_HOST`** — FTP 主機 IP
- **`$FTP_REMOTE_ROOT`** — 伺服器上的目標目錄（如 `/public_html`）
- **`$LOCAL_SOURCE`** — 本地要上傳的資料夾路徑
- **`$EXCLUDE_LIST`** — 不要上傳的檔案或資料夾名稱（如 `.git`, `node_modules`）

---

## 注意事項

- 請勿將此部署資料夾的帳號密碼提交到 public Git 儲存庫
- 第一次執行前請先用 `test-connection.ps1` 確認連線正常
