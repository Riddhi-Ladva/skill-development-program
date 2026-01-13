function orderFood(callback) {
  console.log("Preparing Food...");

  setTimeout(() => {
    callback();
  }, 5000);
}

orderFood(() => {
  console.log("Your Food is ready.🍕");
});
