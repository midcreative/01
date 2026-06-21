import urllib.request, json
req = urllib.request.Request(
    'http://panlingyi.tw/admin/api_receive_opinions.php',
    data=json.dumps({"opinions":[{"candidate_id":1, "candidate_name":"潘炩�?, "title":"test final", "url":"http://test233x", "description":"hello world final"}]}).encode('utf-8'),
    headers={'Content-Type': 'application/json', 'Authorization': 'Bearer ee6947555628e696649eea7f7a0d03c3'}
)
try:
    print(urllib.request.urlopen(req).read().decode('utf-8'))
except urllib.error.HTTPError as e:
    print(f"HTTP ERROR: {e.code}")
    print(e.read().decode('utf-8'))
