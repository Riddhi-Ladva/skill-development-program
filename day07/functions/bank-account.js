let account = {
  name: "Riddhi",
  balance: 2000,
};
console.log("balance before deposit", account.balance);

function deposit(acc, amount) {
  acc.balance += amount;
}

//passed by reference
deposit(account, 2000);

console.log("balance after deposit", account.balance);
