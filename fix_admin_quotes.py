files = [
    'admin/cron/fetch_opinions.php',
    'admin/api_receive_opinions.php',
    'admin/migration_opinion.php',
    'admin/migration_v4_petition_category.php',
    'admin/migration_v5_hero_settings.php',
    'admin/migration_v6_towns.php'
]
for p in files:
    try:
        with open(p, 'r', encoding='utf-8', errors='replace') as f:
            text = f.read()
        lines = text.splitlines()
        fixed = []
        for line in lines:
            if line.endswith(';'):
                if line.count("'") % 2 != 0:
                    line = line[:-1] + "';"
                elif line.count('"') % 2 != 0:
                    line = line[:-1] + '";'
            fixed.append(line)
        with open(p, 'w', encoding='utf-8') as f:
            f.write('\n'.join(fixed))
    except Exception as e:
        pass
print('Fixed quotes in admin files')
