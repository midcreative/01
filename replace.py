import os

replacements = [
    ('demo10.midcreative.com', 'panlingyi.tw'),
    ('$dotenv->load()', '$dotenv->safeLoad()')
]

for root, dirs, files in os.walk('.'):
    # Skip .git and vendor directories
    if '.git' in root or 'vendor' in root or 'node_modules' in root:
        continue
    for file in files:
        if file.endswith(('.php', '.html', '.xml', '.txt', '.md', '.ps1', '.py', '.json')):
            path = os.path.join(root, file)
            # Skip this script
            if path.endswith('replace.py'): continue
            
            try:
                with open(path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                new_content = content
                for old, new in replacements:
                    new_content = new_content.replace(old, new)
                    
                if new_content != content:
                    with open(path, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    print(f"Updated: {path}")
            except Exception as e:
                pass
