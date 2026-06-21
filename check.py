with open('api/petition-propose.php', 'rb') as f: text = f.read().decode('utf-8', errors='replace')
for i, line in enumerate(text.splitlines()):
    if '\ufffd' in line: print(f'{i+1}: {line.encode("ascii", errors="backslashreplace").decode("ascii")}')
