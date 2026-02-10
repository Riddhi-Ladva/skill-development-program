attendance = {
    "Riddhi": ["Mon", "Tue", "Wed", "Thu", "Fri"],
    "Harsh": ["Mon", "Wed", "Fri"],
    "Raj": ["Tue", "Thu"]
}

def check_attendance(name):
    
    
        count = len(attendance[name])
        print(f"{name} attended on {count} days")

        if(count < 3):
            print(f"Warning⚠️ {name} has low attendance")
        else:
            print(f"{name} has good attendance")

check_attendance("Raj")