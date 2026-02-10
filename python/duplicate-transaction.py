transactions = [100, 200, 100, 220, 300, 200]

seen = set()
duplicates = set()

for item in transactions:
    if item in seen:
        duplicates.add(item)
    else:
        seen.add(item)

print("Unique transactions:", seen)
print("Duplicate transactions:", duplicates)
