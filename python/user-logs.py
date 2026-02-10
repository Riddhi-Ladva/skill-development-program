
class user:
    def __init__(self, username, email):
        self.username = username
        self.email = email
        self.password = 123
    
    def check_password(self, password):
        if password == self.password:
            print("Password is correct.")
            f= open("users.txt", "a")
            f.write(f"INFO: User logged in - Username: {self.username}, Email: {self.email}\n")
            f.close()
        else:

            print("Password is incorrect.")
            f= open("users.txt", "a")
            f.write(f"ERROR: Invalid password attempt - Username: {self.username}, Email: {self.email}\n")
            f.close()

username = input("Enter your username: ")
email = input("Enter your email: ")
password = input("Enter your password: ")
new_user = user(username, email)

new_user.check_password(int(password))