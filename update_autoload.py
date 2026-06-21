import os

for root, dirs, files in os.walk('.'):
    if '.git' in root or 'vendor' in root or 'node_modules' in root:
        continue
    for file in files:
        if file.endswith('.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = content.replace('/admin/vendor/autoload.php', '/vendor/autoload.php')
            new_content = new_content.replace('/../admin/vendor/autoload.php', '/../vendor/autoload.php')
            
            if path.startswith('.\\admin\\') or path.startswith('./admin/'):
                # fetch_opinions.php used __DIR__ . '/../vendor/autoload.php' -> which meant /admin/../vendor/autoload.php = /vendor/autoload.php (this was WRONG! It meant /var/www/vendor/autoload.php which didn't exist before)
                # Wait, if fetch_opinions.php is in admin/cron/fetch_opinions.php, __DIR__ is admin/cron.
                # __DIR__ . '/../vendor/autoload.php' is admin/vendor/autoload.php.
                # If we move vendor to root, it should be __DIR__ . '/../../vendor/autoload.php'.
                new_content = new_content.replace("__DIR__ . '/../vendor/autoload.php'", "__DIR__ . '/../../vendor/autoload.php'")
                
                # index.php in admin used __DIR__ . '/vendor/autoload.php', now should be __DIR__ . '/../vendor/autoload.php'
                new_content = new_content.replace("__DIR__ . '/vendor/autoload.php'", "__DIR__ . '/../vendor/autoload.php'")
                
            if new_content != content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Updated {path}')
