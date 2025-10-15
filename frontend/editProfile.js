const editBtn = document.getElementById("edit-btn");
const cancelBtn = document.getElementById("cancel-btn");
const displaySection = document.getElementById("display-section");
const editForm = document.getElementById("edit-form");

editBtn.onclick = () => {
  displaySection.style.display = "none";
  editForm.style.display = "block";
};

cancelBtn.onclick = () => {
  displaySection.style.display = "block";
  editForm.style.display = "none";
};
