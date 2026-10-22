import os
import difflib

csv_working = r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv"
csv_plugins = r"C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv"

with open(csv_working, "r", encoding="utf-8") as f:
    working_lines = f.readlines()

with open(csv_plugins, "r", encoding="utf-8") as f:
    plugins_lines = f.readlines()

print(f"Working CSV lines: {len(working_lines)}, size: {os.path.getsize(csv_working)}")
print(f"Plugins CSV lines: {len(plugins_lines)}, size: {os.path.getsize(csv_plugins)}")

diff = list(difflib.unified_diff(plugins_lines, working_lines, fromfile="plugins_csv", tofile="working_csv"))
print(f"Total diff lines: {len(diff)}")
for line in diff[:30]:
    print(line, end="")
if len(diff) > 30:
    print(f"... and {len(diff) - 30} more lines")
