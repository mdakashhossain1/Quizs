import concurrent.futures
import json
import pathlib
import urllib.request

root = pathlib.Path(__file__).resolve().parent.parent
assets = json.loads((root / 'design/assets.json').read_text())

def download(asset):
    target = root / asset['path']
    target.parent.mkdir(parents=True, exist_ok=True)
    if not target.exists():
        request = urllib.request.Request(asset['url'], headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(request, timeout=60) as response:
            target.write_bytes(response.read())
    return target.name

with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
    results = list(pool.map(download, assets))
print(f'Downloaded {len(results)} Figma assets.')
