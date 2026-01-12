function calculate(op) {
  let x = Number(document.getElementById("firstvalue").value);
  let y = Number(document.getElementById("secondvalue").value);
  let result;

  if (op === "+") {
    result = x + y;
  } else if (op === "-") {
    result = x - y;
  } else if (op === "*") {
    result = x * y;
  } else if (op === "/") {
    if (y === 0) {
      result = "Cannot divide by zero";
    } else {
      result = x / y;
    }
  }

  document.getElementById("ans").textContent = "Answer: " + result;
}
