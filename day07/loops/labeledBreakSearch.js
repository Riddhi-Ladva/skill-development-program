/**
 * File: labeledBreakSearch.js
 * Description: Demonstrates labeled break to exit nested loops
 */

searchStudent:
for (let classNo = 1; classNo <= 10; classNo++) {
  console.log("Class:", classNo);

  for (let rollNo = 1; rollNo <= 20; rollNo++) {
    console.log(" Roll No:", rollNo);

    if (classNo === 2 && rollNo === 18) {
      console.log("🎯 Student Found!");
      break searchStudent;
    }
  }
}
