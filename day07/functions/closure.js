function createBankAccount(initialBalance) {
  let balance = initialBalance; // outer variable

  function deposit(amount) { // named inner function
    balance += amount;
    console.log(`Updated balance: ${balance}`);
  }

  return deposit; // return the inner function
}

let myAccount = createBankAccount(1000);

myAccount(500); // Updated balance: 1500
myAccount(200); // Updated balance: 1700
