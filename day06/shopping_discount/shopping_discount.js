document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("checkBtn").addEventListener("click", function () {
    var bill = Number(document.getElementById("bill").value);
    if (bill >= 1000) {
      document.getElementById("message").innerText =
        "Congrats! You get a discount!";
      document.getElementById("message").style.color = "green";
    } else {
      document.getElementById("message").innerText = "No discount this time.";
      document.getElementById("message").style.color = "orange";
    }
  });
});
