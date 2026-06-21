---
description: GEO 基礎設定 — 讓 AI 爬蟲正確存取並理解網站內容（robots.txt、llms.txt、JSON-LD、meta tags）
---

# GEO Fundamentals Skill — AI 爬蟲可見性基礎設定

本 Skill 定義每次建立或修改網頁時，**必須確保** AI 爬蟲（ChatGPT、Perplexity、Gemini、Claude Bot 等）能正確存取和理解網站內容的基礎規範。

---

## 1. robots.txt — 開放 AI 爬蟲存取

每次部署時，確認 `demo/robots.txt` 包含以下內容，**不可封鎖** AI 爬蟲：

```
User-agent: *
Allow: /

# 明確允許主要 AI 爬蟲
User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: GoogleBot
Allow: /

User-agent: Bingbot
Allow: /

Sitemap: https://panlingyi.tw/demo/sitemap.xml
```

---

## 2. llms.txt — AI 大型語言模型導覽文件

在 `demo/llms.txt` 放置一份給 AI 讀取的網站說明，讓 AI 知道此網站的主題、關鍵人物、服務範疇：

```markdown
# 潘炩禕 服務日記 | 屏東縣議員第三選區

## 網站主旨
本網站是屏東縣議員參選人潘炩禕的官方服務互動站，記錄在地服務案例、政策白皮書，並提供民眾連署與志工招募功能。

## 服務對象
屏東縣第三選區：潮州鎮、內埔鄉、萬巒鄉、枋寮鄉

## 五大服務主軸
1. 農漁牧產銷發展 — 冷鏈系統、品牌化經營
2. 勞資對話與共榮 — 勞工法律諮詢
3. 親子共學與友善環境 — 特色公園、AI 共學
4. 全齡心靈關懷 — 運動、心理諮商
5. 長照 3.0 數位升級 — 遠距健康監測

## 關鍵頁面
- 鄉鎮足跡：/demo/index.html
- 行動白皮書：/demo/index.html#issues
- 連署實證站：/demo/index.html#feedback
- 志工招募：/demo/volunteer.html
```

---

## 3. 每個頁面必備 JSON-LD 結構化資料

### 首頁（Person Schema）

每個 HTML 頁面的 `<head>` 內必須加入對應的 JSON-LD：

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "潘炩禕",
  "jobTitle": "屏東縣議員候選人",
  "description": "屏東縣第三選區縣議員參選人，深耕農漁業、長照、親子教育等在地服務。",
  "areaServed": [
    { "@type": "City", "name": "潮州鎮" },
    { "@type": "City", "name": "內埔鄉" },
    { "@type": "City", "name": "萬巒鄉" },
    { "@type": "City", "name": "枋寮鄉" }
  ],
  "url": "https://panlingyi.tw/demo/",
  "sameAs": []
}
</script>
```

### 服務日記文章（Article Schema）

每篇服務日記必須包含：

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "【文章標題】",
  "description": "【文章摘要，約 50-100 字】",
  "datePublished": "YYYY-MM-DD",
  "author": {
    "@type": "Person",
    "name": "潘炩禕"
  },
  "about": {
    "@type": "City",
    "name": "【鄉鎮名稱】"
  },
  "keywords": "屏東,議員,服務,【主軸關鍵字】"
}
</script>
```

---

## 4. 每個頁面 Meta Tags 標準

每個 `<head>` 必須包含：

```html
<!-- 基礎 SEO -->
<title>【頁面標題】| 潘炩禕 服務日記</title>
<meta name="description" content="【100-160 字的頁面描述，包含地名與主軸關鍵字】">
<meta name="keywords" content="屏東縣議員, 潘炩禕, 潮州鎮, 內埔鄉, 萬巒鄉, 枋寮鄉, 服務日記">

<!-- Open Graph（Facebook/Line 分享預覽） -->
<meta property="og:title" content="【頁面標題】| 潘炩禕">
<meta property="og:description" content="【描述】">
<meta property="og:url" content="https://panlingyi.tw/demo/【路徑】">
<meta property="og:type" content="website">
<meta property="og:locale" content="zh_TW">

<!-- Canonical URL -->
<link rel="canonical" href="https://panlingyi.tw/demo/【路徑】">

<!-- 語言宣告 -->
<html lang="zh-TW">
```

---

## 5. 內容撰寫 GEO 規範（可引用性）

每段服務日記的正文，應符合以下規則，讓 AI 更容易引用：

- ✅ 每段自成一個完整意思（134–167 字最佳）
- ✅ 直接回答問題（如：「潘炩禕在枋寮鄉做了什麼？⟶ 積極爭取冷鏈倉儲設備...」）
- ✅ 包含地名、人名、事件名稱等**實體（Entity）信號**
- ✅ 使用明確的數字（如：已關切 284 項議題）
- ❌ 避免模糊語句（如：「積極推動各項業務」）
