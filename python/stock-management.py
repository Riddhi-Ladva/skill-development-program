inventory = {
    "Laptop": 10,
    "Mouse": 0,
    "Keyboard": 3,
    "Monitor": 25
}

def check_stock(item):
    if item in inventory:
        if inventory[item] > 0:
            print(f"{item} is in stock. Quantity: {inventory[item]}")
        elif inventory[item] <5 and inventory[item] > 0:
            print(f"{item} is low in stock. Quantity: {inventory[item]}")
        else:
            print(f"{item} is out of stock.")

check_stock("Monitor")