class ConfigLoader:
    def __init__(self, filename):
        self.filename = filename
        self.config = {}

    def load_config(self):
        with open(self.filename, "r") as file:
            for line in file:
                key, value = line.strip().split("=")
                self.config[key] = value

    def show_config(self):
        for key in self.config:
            print(f"{key} : {self.config[key]}")

config = ConfigLoader("config.txt")
config.load_config()
config.show_config()
