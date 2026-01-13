/**
 * File: continueExample.js
 * Description: Demonstrates continue statement with labeled loop
 */

const peoples = ["roshni", "rahul", "staff", "suhani"];

checking:
for (let i = 0; i < peoples.length; i++) {

  if (peoples[i] === "staff") {
    console.log("✅ Gate access granted to staff");
    continue checking;
  }

  console.log("🛂 Check passport:", peoples[i]);
  console.log("🧳 Check bags:", peoples[i]);
}
