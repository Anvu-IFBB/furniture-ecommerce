import json
import os

brain_dir = r"C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7"

def inspect_json(name, path):
    print(f"=== {name} ===")
    if not os.path.exists(path):
        print(f"File not found: {path}")
        return
    with open(path, "r", encoding="utf-8") as f:
        data = json.load(f)
    if isinstance(data, list):
        print(f"Top-level is LIST with {len(data)} items")
        print("First 3 items:")
        for item in data[:3]:
            print(" ", item)
        print("Last 3 items:")
        for item in data[-3:]:
            print(" ", item)
        # count passes/fails
        passes = sum(1 for item in data if item.get("status") == "PASS" or item.get("passed") is True)
        fails = sum(1 for item in data if item.get("status") == "FAIL" or item.get("passed") is False)
        print(f"Pass count: {passes}, Fail count: {fails}")
    elif isinstance(data, dict):
        print("Top-level is DICT. Keys:", list(data.keys()))
        for k in ["total_tests", "passed", "failed", "timestamp", "exit_code", "environment"]:
            if k in data:
                print(f"  {k}: {data[k]}")
        if "tests" in data:
            print(f"  tests count: {len(data['tests'])}")
            for t in data["tests"][:3]:
                print("   First tests:", t.get("id"), t.get("name"), t.get("status"))
            for t in data["tests"][-3:]:
                print("   Last tests:", t.get("id"), t.get("name"), t.get("status"))
        elif "results" in data:
            print(f"  results count: {len(data['results'])}")
            for t in data["results"][:3]:
                print("   First results:", t.get("id"), t.get("name"), t.get("status"))
            for t in data["results"][-3:]:
                print("   Last results:", t.get("id"), t.get("name"), t.get("status"))
    print()

inspect_json("gate3g_regression_results.json", os.path.join(brain_dir, "scratch", "gate3g_regression_results.json"))
inspect_json("stage3nr2_runtime_results.json", os.path.join(brain_dir, "scratch", "stage3nr2_runtime_results.json"))
inspect_json("stage3nr_regression_results.json", os.path.join(brain_dir, "scratch", "stage3nr_regression_results.json"))
