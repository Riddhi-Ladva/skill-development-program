/**
 * File: whileLoopATM.js
 * Description: Simulates ATM PIN verification using while loop
 */

const prompt = require("prompt-sync")();

let enteredPin = "";
const correctPin = "1234";

while (enteredPin !== correctPin) {
  enteredPin = prompt("Enter your ATM PIN: ");

  if (enteredPin !== correctPin) {
    console.log("❌ Access Denied");
  } else {
    console.log("✅ Access Granted");
    break;
  }
}
