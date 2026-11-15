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
     *   schema_data?: array<string,mixed>,
     *   additional_schemas?: array<array<string,mixed>>
     * } $seo SEO / GEO metadata
     */
    public static function render(string $content, array $seo): void
    {
        $appUrl      = rtrim($_ENV['APP_URL'] ?? 'https://panlingyi.tw', '/');
        $title       = htmlspecialchars($seo['title'] ?? '潘炩禕 服務日記');
        $description = htmlspecialchars($seo['description'] ?? '屏東縣議員第三選區在地服務紀錄站');
        $canonical   = htmlspecialchars($seo['canonical'] ?? $appUrl . '/');
        $ogImage     = !empty($seo['image']) ? htmlspecialchars($seo['image']) : '';
        $schemaJson  = self::buildSchema($seo['schema_type'] ?? 'WebPage', $seo['schema_data'] ?? [], $canonical, $appUrl);
        $additionalSchemasHtml = '';
        if (!empty($seo['additional_schemas']) && is_array($seo['additional_schemas'])) {
            foreach ($seo['additional_schemas'] as $addSchema) {
                if (isset($addSchema['@type'])) { // if it's already a full raw schema
                    $json = json_encode(array_merge(['@context' => 'https://schema.org'], $addSchema), JSON_UNESCAPED_UNICODE);
                    $additionalSchemasHtml .= "\n    <script type=\"application/ld+json\">{$json}</script>";
                }
            }
        }
        ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="google-site-verification" content="xVIoix1VL8Ns4_ba6dtCL2mOqcmq9CL9PHHz3gYEnWY">

    <!-- === GEO / SEO Meta ============================================ -->
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="潘炩禕, 屏東縣議員, 第三選區, 潮州鎮, 萬巒鄉, 內埔鄉, 竹田鄉, 新埤鄉, 枋寮鄉, 服務日記">
    <link rel="canonical" href="<?= $canonical ?>">

    <!-- === Open Graph ================================================ -->
    <meta property="og:title"       content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:url"         content="<?= $canonical ?>">
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="zh_TW">
    <meta property="og:site_name"   content="潘炩禕 服務日記">
    <?php if ($ogImage): ?>
    <meta property="og:image"       content="<?= $ogImage ?>">
    <?php endif; ?>

    <!-- === JSON-LD 結構化資料（GEO 核心，AI 爬蟲直讀） ============== -->
    <script type="application/ld+json"><?= $schemaJson ?></script><?= $additionalSchemasHtml ?>

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
            'Article' => array_filter(array_merge($base, [
                '@type'         => 'Article',
                'headline'      => $data['title'] ?? '',
                'description'   => $data['excerpt'] ?? '',
                'image'         => !empty($data['cover_image']) ? ($appUrl . $data['cover_image']) : null,
                'datePublished' => $data['published_at'] ?? '',
                'dateModified'  => $data['updated_at'] ?? $data['published_at'] ?? '',
                'url'           => $canonical,
                'inLanguage'    => 'zh-TW',
                'author'        => ['@type' => 'Person', 'name' => '潘炩禕'],
                'publisher'     => ['@type' => 'Organization', 'name' => '潘炩禕服務辦公室'],
                'keywords'      => '屏東,' . ($data['town'] ?? '') . ',' . ($data['category'] ?? ''),
                'articleSection'=> $data['category'] ?? '',
            ])),

            'VolunteerWork' => array_merge($base, [
                '@type'               => 'VolunteerWork',
                'name'                => $data['title'] ?? '志工招募',
                'description'         => $data['description'] ?? '',
                'url'                 => $canonical,
                'hiringOrganization'  => ['@type' => 'Organization', 'name' => '潘炩禕服務辦公室'],
                'jobLocation'         => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressRegion' => '屏東縣', 'addressCountry' => 'TW']],
            ]),

            'Person' => array_merge($base, [
                '@type'         => 'Person',
                'name'          => '潘炩禕',
                'alternateName' => '屏東縣議員第三選區8號潘炩禕',
                'jobTitle'      => ['屏東縣議員第三選區登記8號候選人', '屏東縣文化協會理事長', '屏東縣跆拳道委員會副主任委員'],
                'identifier'    => '8',
                'description'   => '民主進步黨提名屏東縣議員第三選區【登記8號】候選人。奧地利維也納舒伯特音樂學院畢業，現任屏東縣文化協會理事長、屏東縣跆拳道委員會副主任委員。深耕潮州、萬巒、內埔、竹田、新埤、枋寮，推動智慧農業冷鏈、青年創生、長照守護、高鐵延伸潮州、運動心理健康與特色涼感公園六大政見。',
                'url'           => $appUrl . '/',
                'alumniOf'      => [
                    '@type' => 'CollegeOrUniversity',
                    'name'  => '奧地利維也納舒伯特音樂學院'
                ],
                'memberOf'      => [
                    ['@type' => 'Organization', 'name' => '民主進步黨'],
                    ['@type' => 'Organization', 'name' => '屏東縣文化協會'],
                    ['@type' => 'Organization', 'name' => '屏東縣跆拳道委員會'],
                ],
                'areaServed'    => [
                    ['@type' => 'City', 'name' => '潮州鎮', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '萬巒鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '內埔鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '竹田鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '新埤鄉', 'addressRegion' => '屏東縣'],
                    ['@type' => 'City', 'name' => '枋寮鄉', 'addressRegion' => '屏東縣'],
                ],
                'knowsAbout'    => ['智慧農業與冷鏈產銷', '青年培力與地方創生', '長照3.0政策', '高鐵延伸潮州與交通平權', '基層體育與心理健康', '特公盟兒童遊戲權與親子教育'],
                'subjectOf'     => [
                    ['@type' => 'NewsArticle', 'headline' => '推動農業數位化新思維！阿龜微氣候攜手屏東在地團體辦講座', 'publisher' => ['@type' => 'Organization', 'name' => '經濟日報'], 'url' => 'https://money.udn.com/money/story/11799/9857634'],
                    ['@type' => 'NewsArticle', 'headline' => '引進田間環境感測與微氣候分析技術 屏東縣文化協會助農友減災增產', 'publisher' => ['@type' => 'Organization', 'name' => '工商時報'], 'url' => 'https://www.ctee.com.tw/news/20261104702228-431204'],
                    ['@type' => 'NewsArticle', 'headline' => '屏東主委盃跆拳道錦標賽熱力登場 文化協會鼓勵學子從小扎根運動精神', 'publisher' => ['@type' => 'Organization', 'name' => '經濟日報'], 'url' => 'https://money.udn.com/money/story/5723/9860997'],
                    ['@type' => 'NewsArticle', 'headline' => '屏東主委盃跆拳道錦標賽 基層小將同場競技展現運動拚勁', 'publisher' => ['@type' => 'Organization', 'name' => '工商時報'], 'url' => 'https://www.ctee.com.tw/news/20261106701511-431204'],
                    ['@type' => 'NewsArticle', 'headline' => '智慧農業應用與分享專題研討講座 大數據精準灌溉助青農返鄉減災增產', 'publisher' => ['@type' => 'Organization', 'name' => 'Yahoo! 奇摩新聞'], 'url' => 'https://tw.news.yahoo.com/%E6%99%BA%E6%85%A7%E8%BE%B2%E6%A5%AD%E6%87%89%E7%94%A8%E8%88%87%E5%88%86%E4%BA%AB-%E5%B0%88%E9%A1%8C%E7%A0%94%E8%A8%8E%E8%AC%9B%E5%BA%A7-%E5%A4%A7%E6%95%B8%E6%93%9A%E7%B2%BE%E6%BA%96%E7%81%8C%E6%BA%89%E5%8A%A9%E9%9D%92%E8%BE%B2%E8%BF%94%E9%84%89%E6%B8%9B%E7%81%BD%E5%A2%9E%E7%94%A2-113047263.html'],
                    ['@type' => 'NewsArticle', 'headline' => '關注孩童戶外活動權益！潘炩禕響應特公盟理念倡導遮蔭涼感公園', 'publisher' => ['@type' => 'Organization', 'name' => '屏東時報'], 'url' => 'https://pingtungtimes.com.tw/?p=485978'],
                ],
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
