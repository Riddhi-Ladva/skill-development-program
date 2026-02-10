cart=[100,200,600,400,500]

if(sum(cart)>1000):
    print("You get 10% discount")
    print("Your total price is ",sum(cart)*0.9);
elif(sum(cart)>500):
    print("You get 5% discount")
    print("Your total price is ",sum(cart)*0.95);
else:
    print("No discount")
    print("Your total price is ",sum(cart));

