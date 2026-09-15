import concurrent.futures
import io
from pathlib import Path
import urllib.request
import zipfile

root = Path(__file__).resolve().parent.parent / 'assets/fonts'
root.mkdir(parents=True, exist_ok=True)
base = 'https://raw.githubusercontent.com/google/fonts/main/ofl/'
files = {f'Poppins-{weight}.ttf': base + f'poppins/Poppins-{weight}.ttf' for weight in ['Light', 'Regular', 'Medium', 'SemiBold', 'Bold', 'ExtraBold']}
files.update({
    'Poppins-OFL.txt': base + 'poppins/OFL.txt',
    'DaysOne-Regular.ttf': base + 'daysone/DaysOne-Regular.ttf',
    'DaysOne-OFL.txt': base + 'daysone/OFL.txt',
    'Quicksand.ttf': base + 'quicksand/Quicksand%5Bwght%5D.ttf',
    'Quicksand-OFL.txt': base + 'quicksand/OFL.txt',
    'Questrial-Regular.ttf': base + 'questrial/Questrial-Regular.ttf',
    'Questrial-OFL.txt': base + 'questrial/OFL.txt',
    'Quizlo-DEMO.otf': 'https://st.1001fonts.net/download/font/quizlo-demo.regular.otf',
})

def get(url):
    request = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(request, timeout=60) as response:
        return response.read()

def save(item):
    name, url = item
    (root / name).write_bytes(get(url))
    return name

with concurrent.futures.ThreadPoolExecutor(max_workers=6) as pool:
    for name in pool.map(save, files.items()):
        print(name)

with zipfile.ZipFile(io.BytesIO(get('https://dl.dafont.com/dl/?f=happy_school'))) as archive:
    for name in archive.namelist():
        if name.lower().endswith(('.ttf', '.txt')):
            (root / Path(name).name.replace(' ', '-')).write_bytes(archive.read(name))
            print(name)
