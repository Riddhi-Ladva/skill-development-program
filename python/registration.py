"""
Registration validation – industry style
"""

name = input("Enter your name: ")
age = int(input("Enter your age: "))
email = input("Enter your email: ")
password = input("Enter your password: ")

error = False

if len(name) < 5:
    print("❌ Name must be at least 5 characters long")
    error = True

if age < 18:
    print("❌ You must be at least 18 years old")
    error = True

if "@" not in email or "." not in email:
    print("❌ Invalid email address")
    error = True

if len(password) < 8:
    print("❌ Password must be at least 8 characters long")
    error = True

if not any(char.isdigit() for char in password):
    print("❌ Password must contain at least one number")
    error = True

if not error:
    print("✅ Registration successful!")
