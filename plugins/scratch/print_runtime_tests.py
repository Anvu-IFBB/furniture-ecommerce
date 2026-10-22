import json
import os

brain_dir = r"C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7"
path = os.path.join(brain_dir, "scratch", "stage3nr2_runtime_results.json")
with open(path, "r", encoding="utf-8") as f:
    data = json.load(f)

print(f"Total: {len(data)}")
for i, item in enumerate(data):
    print(f"{i+1}. [{item.get('id')}] ({item.get('section')}) {item.get('name')}: passed={item.get('passed')} | {item.get('details')}")
