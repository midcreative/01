import os
import codecs

files = [
    r'admin\crawler.ps1',
    r'admin\爬蟲腳本_手動更新輿情.ps1'
]

for file in files:
    if os.path.exists(file):
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
        with open(file, 'w', encoding='utf-8-sig') as f:
            f.write(content)
        print("Fixed", file)
