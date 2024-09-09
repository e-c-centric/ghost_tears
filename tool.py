import csv
from collections import defaultdict

# Define the input file path
input_file = "words/merged.csv"

# Dictionary to hold rows for each unique digit
data_by_digit = defaultdict(list)

# Read the input file and organize data by the first column digit
with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.reader(f)
    for row in reader:
        digit = row[0]
        data_by_digit[digit].append(row)

# Write separate files for each unique digit
for digit, rows in data_by_digit.items():
    output_file = f"words/merged_{digit}.csv"
    with open(output_file, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerows(rows)

print("Files separated successfully.")