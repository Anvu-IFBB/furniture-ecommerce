import os
import hashlib
import glob

brain_dir = r"C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7"
workspace_plugins = r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins"
workspace_root = r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce"

def get_file_info(filepath):
    if not os.path.exists(filepath):
        return None
    size = os.path.getsize(filepath)
    with open(filepath, "rb") as f:
        content = f.read()
    sha256 = hashlib.sha256(content).hexdigest().upper()
    lines = content.count(b"\n") + (1 if content and not content.endswith(b"\n") else 0)
    return {
        "path": filepath,
        "size": size,
        "sha256": sha256,
        "lines": lines
    }

print("=== ALL IMPORTER COPIES ===")
importer_candidates = [
    os.path.join(workspace_plugins, "run_controlled_import.php"),
    os.path.join(brain_dir, "backups_stage_3nr2_20261019_113000", "run_controlled_import.php"),
    os.path.join(brain_dir, "backups_stage_3nr_20261019_110000", "run_controlled_import.php"),
]
for root, dirs, files in os.walk(brain_dir):
    for f in files:
        if "run_controlled_import" in f and f.endswith(".php"):
            p = os.path.join(root, f)
            if p not in importer_candidates:
                importer_candidates.append(p)

for p in importer_candidates:
    info = get_file_info(p)
    if info:
        print(f"Path: {info['path']}")
        print(f"  Size: {info['size']} bytes | Lines: {info['lines']} | SHA256: {info['sha256']}\n")

print("=== ALL CSV COPIES ===")
csv_candidates = [
    os.path.join(workspace_root, "data-preparation", "woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv"),
    os.path.join(workspace_plugins, "woocommerce_furniture_dataset_22_products.csv"),
]
for root, dirs, files in os.walk(workspace_root):
    for f in files:
        if f.endswith(".csv"):
            p = os.path.join(root, f)
            if p not in csv_candidates:
                csv_candidates.append(p)

for p in csv_candidates:
    info = get_file_info(p)
    if info:
        print(f"Path: {info['path']}")
        print(f"  Size: {info['size']} bytes | Lines: {info['lines']} | SHA256: {info['sha256']}\n")

print("=== ALL TEST HARNESSES AND RUNTIME TESTS ===")
test_candidates = [
    os.path.join(brain_dir, "scratch", "test_harness_php.php"),
    os.path.join(brain_dir, "backups_stage_3nr_20261019_110000", "test_harness_php.php"),
    os.path.join(brain_dir, "scratch", "test_stage3nr2_runtime.php"),
    os.path.join(workspace_plugins, "test_harness_php.php"),
    os.path.join(workspace_plugins, "test_stage3nr2_runtime.php"),
]
for root, dirs, files in os.walk(brain_dir):
    for f in files:
        if ("test_harness" in f or "stage3nr" in f) and f.endswith(".php"):
            p = os.path.join(root, f)
            if p not in test_candidates:
                test_candidates.append(p)

for p in test_candidates:
    info = get_file_info(p)
    if info:
        print(f"Path: {info['path']}")
        print(f"  Size: {info['size']} bytes | Lines: {info['lines']} | SHA256: {info['sha256']}\n")

print("=== ALL RESULT JSON LOGS ===")
json_candidates = [
    os.path.join(brain_dir, "scratch", "gate3g_regression_results.json"),
    os.path.join(brain_dir, "scratch", "stage3nr_regression_results.json"),
    os.path.join(brain_dir, "scratch", "stage3nr2_runtime_results.json"),
    os.path.join(brain_dir, "scratch", "regression_results.json"),
    os.path.join(brain_dir, "scratch", "test_results.json"),
]
for root, dirs, files in os.walk(brain_dir):
    for f in files:
        if f.endswith(".json") and ("result" in f or "regression" in f or "runtime" in f):
            p = os.path.join(root, f)
            if p not in json_candidates:
                json_candidates.append(p)

for p in json_candidates:
    info = get_file_info(p)
    if info:
        print(f"Path: {info['path']}")
        print(f"  Size: {info['size']} bytes | Lines: {info['lines']} | SHA256: {info['sha256']}\n")
