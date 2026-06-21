import os

with open('index.php', 'rb') as f:
    content = f.read().decode('utf-8', errors='replace')

new_settings = """// Default settings fallbacks
$defaultSettings = [
    'HERO_TAG' => '屏東縣議員第三選區參選人',
    'HERO_HOME_TITLE_1' => '聽見地方的心跳，',
    'HERO_HOME_TITLE_2' => '讓服務的溫度延續。',
    'HERO_ISSUES_TITLE_1' => '迎接下個階段的託付，',
    'HERO_ISSUES_TITLE_2' => '設計出更好的屏東。',
    'HERO_FEEDBACK_TITLE_1' => '我們需要你的志願，',
    'HERO_FEEDBACK_TITLE_2' => '翻轉家鄉的未來。',
    'HERO_CTA_SHOW' => '1',
    'HERO_CTA_TEXT' => '參與行動實踐',
    'HERO_BG_IMAGE' => '',
];"""

content_lines = content.splitlines()
start_idx = -1
end_idx = -1
for i, line in enumerate(content_lines):
    if '// Default settings fallbacks' in line:
        start_idx = i
    if start_idx != -1 and '];' in line and i > start_idx:
        end_idx = i
        break

if start_idx != -1 and end_idx != -1:
    content_lines[start_idx:end_idx+1] = new_settings.splitlines()
    with open('index.php', 'w', encoding='utf-8') as f:
        f.write('\n'.join(content_lines))
    print('Fixed settings.')
else:
    print('Could not find settings block.')
