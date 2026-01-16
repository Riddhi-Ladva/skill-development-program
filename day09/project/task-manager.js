const input = document.getElementById("taskInput");
const addBtn = document.getElementById("addBtn");
const list = document.getElementById("taskList");
const msg = document.getElementById("msg");

addBtn.onclick = function () {

  if (input.value === "") {
    msg.innerText = "❌ Task cannot be empty";
    msg.style.color = "red";
    return;
  }

  msg.innerText = "";

  const li = document.createElement("li");
  li.innerText = input.value;

  const completeBtn = document.createElement("button");
  completeBtn.innerText = "✅";

  const deleteBtn = document.createElement("button");
  deleteBtn.innerText = "❌";

  completeBtn.onclick = function (event) {
    event.stopPropagation();
    li.style.backgroundColor = "lightgreen";
  };

  deleteBtn.onclick = function (event) {
    event.stopPropagation();
    li.remove();
  };

  li.onclick = function () {
    li.style.textDecoration = "line-through";
  };

  li.appendChild(completeBtn);
  li.appendChild(deleteBtn);
  list.appendChild(li);

  input.value = "";
};
