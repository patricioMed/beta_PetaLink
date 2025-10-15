document.getElementById("notifBell").addEventListener("click", function (e) {
  e.preventDefault();

  // Reset notifications count to 0 in UI
  const notifCount = document.getElementById("notifCount");
  if (notifCount) notifCount.textContent = "0";

  // Mark notifications as viewed in backend
  fetch("notification.php", { method: "POST" })
    .then(() => {
      // Go to notifications page
      window.location.href = "notification.php";
    })
    .catch((err) => console.error(err));
});

// 🔄 Auto-refresh the page every 30 seconds (30,000 ms)
setInterval(() => {
  location.reload();
}, 30000);
