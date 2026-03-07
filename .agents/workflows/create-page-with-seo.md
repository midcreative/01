---
description: 建立新頁面的標準流程 — 包含設計風格規範、GEO SEO 必備元素、JSON-LD Schema 套用，確保每個新頁面符合 AI 可引用標準
---

# Create Page With SEO Skill — 新頁面建立標準流程

每次為潘炩禕網站新增頁面時，依照以下步驟執行，確保設計一致且符合 GEO 規範。

---

## 步驟 1：確認頁面資訊

建立任何新頁面前，先確認以下資訊：

```
頁面名稱（繁中）：
頁面檔案名稱（英文小寫-連字號）：例如 volunteer.html
頁面類型：[ ] 一般資訊頁  [ ] 服務日記列表  [ ] 表單頁  [ ] 其他
對應的 Schema 類型：[ ] WebPage  [ ] Article  [ ] Event  [ ] FAQPage
主要關鍵字（3-5 個）：
目標地區：[ ] 全區  [ ] 潮州鎮  [ ] 內埔鄉  [ ] 萬巒鄉  [ ] 枋寮鄉
```

---

## 步驟 2：HTML 頁面基礎模板

每個新頁面必須使用以下模板結構：

```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- === GEO / SEO Meta Tags === -->
    <title>【頁面標題 30-60字】| 潘炩禕 服務日記</title>
    <meta name="description" content="【100-160字描述，包含地名與主軸關鍵字】">
    <meta name="keywords" content="屏東縣議員, 潘炩禕, 【主要關鍵字】, 潮州鎮, 枋寮鄉">
    <link rel="canonical" href="https://demo10.midcreative.com/demo/【檔案名稱】">
    
    <!-- === Open Graph === -->
    <meta property="og:title" content="【頁面標題】| 潘炩禕">
    <meta property="og:description" content="【og 描述】">
    <meta property="og:url" content="https://demo10.midcreative.com/demo/【檔案名稱】">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="zh_TW">
    <meta property="og:site_name" content="潘炩禕 服務日記">

    <!-- === JSON-LD 結構化資料（見步驟 3） === -->
    <script type="application/ld+json">
    { /* 依頁面類型填入 */ }
    </script>

    <!-- === 字型 === -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@700;900&family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- === Tailwind CDN（開發用，正式可替換為本地版） === -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- === Lucide Icons === -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- === 設計系統 CSS（品牌色與共用樣式，每頁必須一致） === -->
    <style>
        body { font-family: 'Noto Sans TC', sans-serif; background-color: #F9FBFA; color: #1e293b; -webkit-font-smoothing: antialiased; }
        .font-serif { font-family: 'Noto Serif TC', serif; }
        /* 品牌主色 #66C2A5 — 勿隨意更改 */
        .brand-green { color: #66C2A5; }
        .bg-brand-green { background-color: #66C2A5; }
        .border-brand-green { border-color: #66C2A5; }
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="text-left select-none md:select-auto">

    <!-- === 導航（與 index.html 保持一致，複製 <nav> 區塊） === -->
    <!-- [COPY NAV FROM index.html] -->

    <!-- === 主內容 === -->
    <main class="max-w-7xl mx-auto px-4 py-8 md:py-12">
        <h1 class="text-3xl md:text-5xl font-serif font-black text-slate-900 mb-6 leading-tight">
            【頁面主標題】
        </h1>
        <!-- 內容區塊 -->
    </main>

    <!-- === Footer（與 index.html 保持一致，複製 <footer> 區塊） === -->
    <!-- [COPY FOOTER FROM index.html] -->

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
```

---

## 步驟 3：依頁面類型套用 JSON-LD

### 一般資訊頁（WebPage）
```json
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "【頁面標題】",
  "description": "【頁面描述】",
  "url": "https://demo10.midcreative.com/demo/【路徑】",
  "inLanguage": "zh-TW",
  "author": { "@type": "Person", "name": "潘炩禕" }
}
```

### 服務日記文章（Article）
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "【文章標題】",
  "description": "【文章摘要】",
  "datePublished": "YYYY-MM-DD",
  "dateModified": "YYYY-MM-DD",
  "author": { "@type": "Person", "name": "潘炩禕" },
  "keywords": "屏東, 【鄉鎮】, 【分類主軸】",
  "articleSection": "【農漁牧業關注 / 親子教育 / 長照 / 勞資 / 身心靈】"
}
```

### 志工招募頁（JobPosting）
```json
{
  "@context": "https://schema.org",
  "@type": "VolunteerWork",
  "name": "【志工職缺名稱】",
  "description": "【志工工作描述】",
  "hiringOrganization": {
    "@type": "Organization",
    "name": "潘炩禕服務辦公室"
  },
  "jobLocation": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressRegion": "屏東縣",
      "addressCountry": "TW"
    }
  }
}
```

### FAQ 頁（FAQPage）— 有問答格式的頁面必用
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "【問題】",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "【答案，建議 50-150 字，自成完整意思】"
      }
    }
  ]
}
```

---

## 步驟 4：設計規範 Checklist

```
[ ] 品牌色 #66C2A5 統一使用 .brand-green / .bg-brand-green
[ ] 字型：標題用 Noto Serif TC，內文用 Noto Sans TC
[ ] 圓角風格：卡片使用 rounded-[2rem] 或 rounded-[2.5rem]（保持一致）
[ ] 間距：段落間距 space-y-6 或 gap-8
[ ] 行動裝置：確認 sm/md/lg 響應式斷點均有測試
[ ] 圖示：統一使用 Lucide Icons（lucide.createIcons() 在頁底呼叫）
[ ] 導航列：與 index.html 完全一致（複製貼上，不另做設計）
[ ] Footer：與 index.html 完全一致
```

---

## 步驟 5：將新頁面加入 sitemap.xml

建立新頁面後，在 `demo/sitemap.xml` 新增：

```xml
<url>
  <loc>https://demo10.midcreative.com/demo/【檔案名稱】</loc>
  <lastmod>YYYY-MM-DD</lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.8</priority>
</url>
```

---

## 步驟 6：執行 GEO Audit 並部署

```
1. 完成頁面後，執行 .agents/workflows/seo-geo-audit.md 的審計流程
2. 全部通過後，執行 .\deploy\deploy.ps1 -Force
```
