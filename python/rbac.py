users = {
    "Riddhi": "admin",
    "Harsh": "editor",
    "Sonal": "viewer"
}

permission = {
    "admin": ["read", "write", "delete"],
    "editor": ["read", "write"],    
    "viewer": ["read"]
}

def check_permission(user, action):
    if user in users:                     # ✅ Step 1
        role = users[user]               # ✅ Step 2
        
        if action in permission[role]:   # ✅ Step 3
            print("Action allowed")
        else:
            print("Action denied: insufficient permissions")
    else:
        print("User not found")

check_permission("Harsh", "read")
