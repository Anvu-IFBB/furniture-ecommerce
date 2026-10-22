import os
import hashlib

snapshots = [
    r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\sql\local.sql",
    r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql",
    r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql",
    r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_pre_import_snapshot.sql"
]

print("=== SQL SNAPSHOT FILES ===")
for s in snapshots:
    if os.path.exists(s):
        size = os.path.getsize(s)
        with open(s, "rb") as f:
            h = hashlib.sha256(f.read()).hexdigest().upper()
        print(f"Path: {s}")
        print(f"  Size: {size} bytes | SHA256: {h}")
    else:
        print(f"Path: {s} -> NOT FOUND")
