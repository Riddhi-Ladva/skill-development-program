const data = {
  car: [
    {
      brand: "BMW",
      model: ["a1", "b1", "c1"],
    },
    {
      brand: "Audi",
      model: ["a2", "b2", "c2"],
    },
    {
      brand: "Mercedes",
      model: ["a3", "b3", "c3"],
    },
  ],
};

for (let i in data.car) {
  console.log("Brand name:",data.car[i].brand);
  console.log(" ");

  for (let j in data.car[i].model) {
    console.log("Model no:",data.car[i].model[j]);
    console.log(" ");
  }
}
