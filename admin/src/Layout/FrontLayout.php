<?php

declare(strict_types=1);

namespace App\Layout;

/**
 * GEO-optimised base layout for PUBLIC front-end pages.
 *
 * Every PHP SSR page calls FrontLayout::render() with SEO metadata,
 * ensuring AI crawlers (GPTBot, ClaudeBot, PerplexityBot) can read
 * all content without executing JavaScript.
 */
final class FrontLayout
{
    /**
     * Render a complete public page with GEO-ready <head>.
     *
     * @param string $content     HTML body content
     * @param array{
     *   title: string,
     *   description: string,
     *   canonical?: string,
     *   schema_type?: string,
     *   schema_data?: array<string,mixed>
     * } $seo SEO / GEO metadata
     */
    public static function render(string $content, array $seo): void
    {
        $appUrl      = rtrim($_ENV['APP_URL'] ?? 'https://panlingyi.tw', '/');
        $title       = htmlspecialchars($seo['title'] ?? '潘炩禕 服務日記');
        $description = htmlspecialchars($seo['description'] ?? '屏東縣議員第三選區在地服務紀錄站');
        $canonical   = htmlspecialchars($seo['canonical'] ?? $appUrl . '/');
        $schemaJson  = self::buildSchema($seo['schema_type'] ?? 'WebPage', $seo['schema_data'] ?? [], $canonical, $appUrl);
        ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- === GEO / SEO Meta ============================================ -->
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="潘炩禕, 屏東縣議員, 第三選區, 潮州鎮, 內埔鄉, 萬巒鄉, 枋寮鄉, 服務日記">
    <link rel="canonical" href="<?= $canonical ?>">

    <!-- === Open Graph ================================================ -->
    <meta property="og:title"       content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:url"         content="<?= $canonical ?>">
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="zh_TW">
    <meta property="og:site_name"   content="潘炩禕 服務日記">

    <!-- === JSON-LD 結構化資料（GEO 核心，AI 爬蟲直讀） ============== -->
    <script type="application/ld+json"><?= $schemaJson ?></script>

    <!-- === 字型 ====================================================== -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@700;900&family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">

    <!-- === 樣式 ====================================================== -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Noto Sans TC', sans-serif; background-color: #F9FBFA; color: #1e293b; -webkit-font-smoothing: antialiased; }
        .font-serif  { font-family: 'Noto Serif TC', serif; }
        .brand-green { color: #66C2A5; }
        .bg-brand-green   { background-color: #66C2A5; }
        .border-brand-green { border-color: #66C2A5; }
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        * { -webkit-tap-highlight-color: transparent; }
        .card-shadow { box-shadow: 0 10px 30px -10px rgba(102, 194, 165, 0.15); }
    </style>
</head>
<body class="text-left select-none md:select-auto">
<?= $content ?>
<script>lucide.createIcons();</script>
</body>
</html>
        <?php
    }

    /**
     * Build the correct JSON-LD Schema based on page type.
     *
     * @param string               $type       Schema.org @type
     * @param array<string, mixed> $data       Page-specific data
     * @param string               $canonical  Canonical URL
     * @param string               $appUrl     Site base URL
     */
    private static function buildSchema(string $type, array $data, string $canonical, string $appUrl): string
    {
        $base = ['@context' => 'https://schema.org'];

        $schema = match ($type) {
            'Article' => array_merge($base, [
                '@type'         => 'Article',
                'headline'      => $data['title'] ?? '',
                'description'   => $data['excerpt'] ?? '',
                'datePublished' => $data['published_at'] ?? '',
                'dateModified'  => $data['updated_at'] ?? $data['published_at'] ?? '',
                'url'           => $canonical,
                'inLanguage'    => 'zh-TW',
                'author'        => ['@type' => 'Person', 'name' => '潘炩禕'],
                'publisher'     => ['@type' => 'Organization', 'name' => '潘炩禕服務辦公室'],
                'keywords'      => '屏東,' . ($data['town'] ?? '') . ',' . ($data['category'] ?? ''),
                'articleSection'=> $data['category'] ?? '',
            ]),

            'VolunteerWork' => array_merge($base, [
                '@type'               => 'VolunteerWork',
                'name'                => $data['title'] ?? '志工招募',
                'description'         => $data['description'] ?? '',
                'url'                 => $canonical,
                'hiringOrganization'  => ['@type' => 'Organization', 'name' => '潘炩禕服務辦公室'],
                'jobLocation'         => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressRegion' => '屏東縣', 'addressCountry' => 'TW']],
            ]),

            'Person' => array_merge($base, [
                '@type'       => 'Person',
                'name'        => '潘炩禕',
                'jobTitle'    => '屏東縣議員第三選區參選人',
                'description' => '屏東縣議員第三選區參選人，深耕農漁牧產銷、長照數位升級、親子教育、勞資共榮與身心靈關懷五大服務主軸。',
                'url'         => $appUrl . '/',
                'areaServed'  => [
                    ['@type' => 'City', 'name' => '潮州鎮', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '內埔鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '萬巒鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '枋寮鄉', 'addressRegion' => '屏東縣'],
                ],
                'knowsAbout' => ['農漁牧產銷', '長照政策', '親子教育', '勞工權益', '心靈關懷'],
            ]),

            default => array_merge($base, [
                '@type'       => 'WebPage',
                'name'        => $data['title'] ?? '潘炩禕服務日記',
                'description' => $data['description'] ?? '',
                'url'         => $canonical,
                'inLanguage'  => 'zh-TW',
            ]),
        };

        return (string) json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
