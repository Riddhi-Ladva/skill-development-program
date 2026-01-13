/**
 * File: doWhileGame.js
 * Description: Uses do-while loop to ensure game runs at least once
 */

const prompt = require("prompt-sync")();

let playAgain = "";

do {
  console.log("🎮 Playing Game...");
  playAgain = prompt("Play Again? (y/n): ");
} while (playAgain === "y");

console.log("❌ Game Over");
