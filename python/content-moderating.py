comment = "This is stupid and I don't like it."

banned_words = ["stupid", "hate", "dumb"]

if(any(word in comment for word in banned_words)):
    print("Comment contains inappropriate language and cannot be posted.")

else:
    print("Comment posted successfully.")