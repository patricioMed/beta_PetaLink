document.getElementById("goRegister").addEventListener("click", function () {
  window.location.href = "register.html";
});

document.querySelectorAll(".star-rating span").forEach((star) => {
  star.addEventListener("click", async () => {
    const flowerId = star.parentElement.getAttribute("data-flower-id");
    const rating = star.getAttribute("data-value");

    const response = await fetch("save_rating.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ flower_id: flowerId, rating: rating }),
    });

    const result = await response.json();
    if (result.success) {
      alert("Thank you for rating!");
      star.parentElement.querySelector(
        ".avg-rating-text"
      ).textContent = `Avg: ${result.avg_rating}★`;
    } else {
      alert(result.message || "Error saving rating.");
    }
  });
});
