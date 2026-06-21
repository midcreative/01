import os

for root, dirs, files in os.walk('.'):
    if '.git' in root or 'vendor' in root or 'node_modules' in root:
        continue
    for file in files:
        if file.endswith('.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8', errors='replace') as f:
                content = f.read()
            
            new_content = content.replace('/admin/vendor/autoload.php', '/vendor/autoload.php')
            new_content = new_content.replace('/../admin/vendor/autoload.php', '/../vendor/autoload.php')
            
            if path.startswith('.\\admin\\') or path.startswith('./admin/'):
                new_content = new_content.replace("__DIR__ . '/../vendor/autoload.php'", "__DIR__ . '/../../vendor/autoload.php'")
                new_content = new_content.replace("__DIR__ . '/vendor/autoload.php'", "__DIR__ . '/../vendor/autoload.php'")
                
            if new_content != content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Updated {path}')
