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
        $title       = htmlspecialchars($seo['title'] ?? 'æ½˜ç‚©ç¦??å??¥è?');
        $description = htmlspecialchars($seo['description'] ?? 'å±æ±ç¸?­°?¡ç¬¬ä¸‰é¸?€?¨åœ°?å?ç´€?„ç?');
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
    <meta name="keywords" content="æ½˜ç‚©ç¦? å±æ±ç¸?­°?? ç¬¬ä??¸å?, æ½®å??? ?°åŸ¤?? ?§å??? ?¬å??? ç«¹ç”°?? ?‹å¯®?? ?å??¥è?">
    <link rel="canonical" href="<?= $canonical ?>">

    <!-- === Open Graph ================================================ -->
    <meta property="og:title"       content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:url"         content="<?= $canonical ?>">
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="zh_TW">
    <meta property="og:site_name"   content="æ½˜ç‚©ç¦??å??¥è?">

    <!-- === JSON-LD çµæ??–è??™ï?GEO ?¸å?ï¼ŒAI ?¬èŸ²?´è?ï¼?============== -->
    <script type="application/ld+json"><?= $schemaJson ?></script>

    <!-- === å­—å? ====================================================== -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@700;900&family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">

    <!-- === æ¨?? ====================================================== -->
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
                'author'        => ['@type' => 'Person', 'name' => 'æ½˜ç‚©ç¦?],
                'publisher'     => ['@type' => 'Organization', 'name' => 'æ½˜ç‚©ç¦•æ??™è¾¦?¬å®¤'],
                'keywords'      => 'å±æ±,' . ($data['town'] ?? '') . ',' . ($data['category'] ?? ''),
                'articleSection'=> $data['category'] ?? '',
            ]),

            'VolunteerWork' => array_merge($base, [
                '@type'               => 'VolunteerWork',
                'name'                => $data['title'] ?? 'å¿—å·¥?›å?',
                'description'         => $data['description'] ?? '',
                'url'                 => $canonical,
                'hiringOrganization'  => ['@type' => 'Organization', 'name' => 'æ½˜ç‚©ç¦•æ??™è¾¦?¬å®¤'],
                'jobLocation'         => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressRegion' => 'å±æ±ç¸?, 'addressCountry' => 'TW']],
            ]),

            'Person' => array_merge($base, [
                '@type'       => 'Person',
                'name'        => 'æ½˜ç‚©ç¦?,
                'jobTitle'    => 'å±æ±ç¸?­°?¡ç¬¬ä¸‰é¸?€?ƒé¸äº?,
                'description' => 'å±æ±ç¸?­°?¡ç¬¬ä¸‰é¸?€?ƒé¸äººï?æ·±è€•è¾²æ¼ç‰§?¢éŠ·?é•·?§æ•¸ä½å?ç´šã€è¦ªå­æ??²ã€å?è³‡å…±æ¦®è?èº«å??ˆé??·ä?å¤§æ??™ä¸»è»¸ã€?,
                'url'         => $appUrl . '/',
                'areaServed'  => [
                    ['@type' => 'City', 'name' => 'æ½®å???, 'addressRegion' => 'å±æ±ç¸?],
                    ['@type' => 'City', 'name' => '?°åŸ¤??, 'addressRegion' => 'å±æ±ç¸?],
                    ['@type' => 'City', 'name' => '?§å???, 'addressRegion' => 'å±æ±ç¸?],
                    ['@type' => 'City', 'name' => '?¬å???, 'addressRegion' => 'å±æ±ç¸?],
                    ['@type' => 'City', 'name' => 'ç«¹ç”°??, 'addressRegion' => 'å±æ±ç¸?],
                    ['@type' => 'City', 'name' => '?‹å¯®??, 'addressRegion' => 'å±æ±ç¸?],
                ],
                'knowsAbout' => ['è¾²æ??§ç”¢??, '?·ç…§?¿ç?', 'è¦ªå??™è‚²', '?žå·¥æ¬Šç?', 'å¿ƒé??œæ‡·'],
            ]),

            default => array_merge($base, [
                '@type'       => 'WebPage',
                'name'        => $data['title'] ?? 'æ½˜ç‚©ç¦•æ??™æ—¥è¨?,
                'description' => $data['description'] ?? '',
                'url'         => $canonical,
                'inLanguage'  => 'zh-TW',
            ]),
        };

        return (string) json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
