
class user:
    def __init__(self, username, email, password=None):
        self.username = username
        self.email = email
        self.password = password

    def display_info(self):
        print(f"Username: {self.username}, Email: {self.email}")

username = input("Enter your username: ")
email = input("Enter your email: ")
password = input("Enter your password: ")

new_user = user(username, email, password)
new_user.display_info()

f = open("users.txt", "a")
f.write(f"name: {new_user.username}, email: {new_user.email}\n")
f.close()