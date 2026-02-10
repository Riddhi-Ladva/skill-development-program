password = input("Enter your password: ")

if(len(password) < 8):
    print("❌ Password must be at least 8 characters long")

    if(not(any(char.isdigit() for char in password))):
        print("❌ Password must contain at least one number")

    if(not(any(char.isupper() for char in password))):
        print("❌ Password must contain at least one uppercase letter")

    if(not(any(char.islower() for char in password))):
        print("❌ Password must contain at least one lowercase letter")

    if(not(any(char in "!@#$%^&*()-_=+[]{}|;:'\",.<>?/" for char in password))):
        print("❌ Password must contain at least one special character")
else:
    print("✅ Password is strong!")