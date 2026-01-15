const users = [
  { name: "Riddhi", role: "Developer", active: true },
  { name: "Harsh", role: "Designer", active: false },
  { name: "Aman", role: "Tester", active: true }
];

// print all users
for (let user of users) {
  console.log(user.name + " - " + user.role);
}

// find a particular user
const riddhi = users.find(user => user.name === "Riddhi");

// check conditions
const hasInactiveUser = users.some(user => user.active === false);
const allUsersActive = users.every(user => user.active === true);

// create new array of names
const userNames = users.map(user => user.name);

// count active users
const activeUserCount = users.reduce((count, user) => {
  return user.active ? count + 1 : count;
}, 0);

// final output
console.log("Selected User:", riddhi);
console.log("All User Names:", userNames);
console.log("Has Inactive User:", hasInactiveUser);
console.log("Are All Users Active:", allUsersActive);
console.log("Active Users Count:", activeUserCount);
