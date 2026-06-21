with open('index.php', 'rb') as f: lines = f.readlines()
for i in range(75, 90):
    if i < len(lines): print(f'{i+1}: {lines[i].decode(errors="replace").encode("ascii", errors="backslashreplace").decode("ascii").strip()}')
