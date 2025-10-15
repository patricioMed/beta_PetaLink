document.querySelector("form").addEventListener("submit", function (e) {
  e.preventDefault(); // Stop normal form submit

  let formData = new FormData(this);

  fetch("uploadVerification.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      alert("Upload successful! ✅\n\n" + data);
    })
    .catch((error) => {
      alert("Error uploading files ❌");
      console.error(error);
    });
});
