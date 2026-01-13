/**
 * File: forLoopMarks.js
 * Description: Demonstrates for-loop usage to update array values
 */

const marks = [10, 20, 30, 40];

for (let i = 0; i < marks.length; i++) {
  marks[i] += 10; // adding grace marks
}

console.log("Updated Marks:", marks);
