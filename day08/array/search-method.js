/* any element in an array appears more than once
 using indexOf() and lastIndexOf(). */

const arr = ["apple", "banana", "orange", "apple", "mango"];

let hasDuplicate = false;

for (let i = 0; i < arr.length; i++) {
  if (arr.indexOf(arr[i]) !== arr.lastIndexOf(arr[i])) {
    hasDuplicate = true;
    console.log("Duplicate found:", arr[i]);
    break;
  }
}

console.log("Has any element with 2 occurrences?", hasDuplicate);
