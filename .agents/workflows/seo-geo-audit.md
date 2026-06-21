---
description: 部署前 GEO 審計 Checklist — 每次執行 deploy.ps1 之前，用此 Skill 驗證網站符合 AI SEO 規範
---

# SEO & GEO Audit Skill — 部署前審計流程

每次執行 `.\deploy\deploy.ps1` 之前，依照以下 Checklist 逐項驗證。

---

## 步驟 1：技術 SEO 基礎檢查

針對每個即將部署的 HTML 頁面，確認以下項目：

```
[ ] <html lang="zh-TW"> 已設定
[ ] <title> 標籤存在且長度在 30-60 字元之間
[ ] <meta name="description"> 存在且長度在 100-160 字元之間
[ ] <meta name="viewport"> 已設定（行動裝置相容）
[ ] <link rel="canonical"> 指向正確的完整 URL
[ ] 每個頁面只有一個 <h1> 標籤
[ ] 圖片都有 alt 屬性
[ ] 圖片使用 loading="lazy"（首屏以下的圖片）
```

---

## 步驟 2：GEO / AI 爬蟲檢查

```
[ ] demo/robots.txt 存在，且未封鎖 GPTBot、ClaudeBot、PerplexityBot
[ ] demo/llms.txt 存在且內容為最新版本
[ ] demo/sitemap.xml 存在且包含所有頁面 URL
[ ] 每個頁面的 <head> 內有對應類型的 JSON-LD Schema
[ ] JSON-LD 中的日期、地名、人名資料為最新
```

驗證 robots.txt 是否允許 AI 爬蟲，執行下列指令：
```powershell
$r = Invoke-WebRequest "https://panlingyi.tw/demo/robots.txt" -UseBasicParsing
$r.Content | Select-String "GPTBot|ClaudeBot|PerplexityBot"
```
應出現 `Allow: /`，若顯示 `Disallow`，需立即修正。

---

## 步驟 3：Open Graph 社群分享預覽

```
[ ] og:title 已設定
[ ] og:description 已設定（建議與 meta description 相同）
[ ] og:url 指向正確的 canonical URL
[ ] og:locale 設定為 zh_TW
```

---

## 步驟 4：Core Web Vitals 快速檢查

```
[ ] 未使用 Tailwind CDN（應使用本地版或直接寫 CSS）
[ ] 大圖片已壓縮（建議 WebP 格式，< 200KB）
[ ] 外部字型使用 display=swap（Noto Fonts 已套用）
[ ] 主要內容在 JavaScript 載入前即可見（非 SPA 全依賴 JS 渲染）
```

---

## 步驟 5：內容可引用性快速審查

在部署前，針對新增或修改的服務日記，確認：

```
[ ] 每段文字自成完整意思，不需要前後文也能理解
[ ] 包含明確的地名（潮州鎮/內埔鄉/萬巒鄉/枋寮鄉）
[ ] 包含明確的行動（如：「爭取縣府撥款」、「已發函教育處」）
[ ] 包含狀態標籤（如：「專案跟進」/「已發函」/「已完成」）
[ ] 避免空洞語句（如：「積極推動」應改為「預計 Q2 完成提案」）
```

---

## 步驟 6：部署後確認

部署完成後，執行以下驗證：

```powershell
# 確認首頁可存取
Invoke-WebRequest "https://panlingyi.tw/demo/" -UseBasicParsing | Select-Object StatusCode

# 確認 llms.txt 可存取
Invoke-WebRequest "https://panlingyi.tw/demo/llms.txt" -UseBasicParsing | Select-Object StatusCode

# 確認 sitemap 可存取
Invoke-WebRequest "https://panlingyi.tw/demo/sitemap.xml" -UseBasicParsing | Select-Object StatusCode
```

全部回傳 `StatusCode: 200` 即審計通過。

---

## 快速使用指令

完整審計完成後，執行部署：

```powershell
# 1. 預覽
.\deploy\deploy.ps1 -DryRun

# 2. 確認無誤後正式部署
.\deploy\deploy.ps1 -Force

# 3. 部署後確認上述步驟 6
```
