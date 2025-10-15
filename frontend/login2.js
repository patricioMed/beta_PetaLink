// document
//   .getElementById("loginForm")
//   .addEventListener("submit", async function (event) {
//     event.preventDefault();

//     const username = document.getElementById("username").value.trim();
//     const password = document.getElementById("password").value.trim();
//     const errorElem = document.getElementById("error");
//     errorElem.textContent = "";

//     if (!username || !password) {
//       errorElem.textContent = "Please enter both username and password.";
//       return;
//     }

//     try {
//       const response = await fetch("../backend/login.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({ username, password }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         // Redirect to dashboard
//         window.location.href = "shop.php";
//       } else {
//         errorElem.textContent = result.message || "Login failed. Try again.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Please try later.";
//     }
//   });
// document.getElementById("goRegister").addEventListener("click", function () {
//   window.location.href = "register.html";
// });
// 🔒 Password Toggle
const passwordInput = document.getElementById("password");
const toggleIcon = document.getElementById("togglePassword");

// Eye toggle
toggleIcon.addEventListener("click", () => {
  const isPassword = passwordInput.type === "password";
  passwordInput.type = isPassword ? "text" : "password";
  toggleIcon.src = isPassword ? "Images/showEye.png" : "Images/hiddenEye.png";
});

// Modal references
const modal = document.getElementById("statusModal");
const closeModal = document.getElementById("closeModal");
const okButton = document.getElementById("okButton");

// Close modal on click
closeModal.addEventListener("click", () => (modal.style.display = "none"));
okButton.onclick = () => (modal.style.display = "none");
okButton.onclick = () => {
  modal.style.display = "none";
  location.reload(); // 🔄 refresh page
};
window.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});
window.onclick = (event) => {
  if (event.target === modal) {
    modal.style.display = "none";
  }
};

// Register redirect
document.getElementById("goRegister").addEventListener("click", function () {
  window.location.href = "register.html";
});

// Login handler
document
  .getElementById("loginForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = passwordInput.value.trim();
    const errorElem = document.getElementById("error");
    errorElem.textContent = "";

    if (!email || !password) {
      errorElem.textContent = "Please enter both email and password.";
      return;
    }

    try {
      const response = await fetch("../backend/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      const result = await response.json();

      if (result.success) {
        switch (result.role) {
          case "owner":
            window.location.href = "flowershopOwner.php";
            break;
          case "customer":
            window.location.href = "home.php";
            break;
          case "admin":
            window.location.href = "admin_dashboard.php";
            break;
          default:
            errorElem.textContent = "Unrecognized user role.";
        }
      } else {
        // ✅ Show modal if account not approved
        if (result.message && result.message.includes("not approved")) {
          modal.style.display = "flex";
        } else {
          errorElem.textContent = result.message || "Login failed. Try again.";
        }
      }
    } catch (error) {
      errorElem.textContent = "Server error. Please try later.";
    }
  });

// original code >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// const passwordInput = document.getElementById("password");
// const toggleIcon = document.getElementById("togglePassword");

// toggleIcon.addEventListener("click", () => {
//   const isPassword = passwordInput.type === "password";
//   passwordInput.type = isPassword ? "text" : "password";
//   toggleIcon.src = isPassword ? "Images/showEye.png" : "Images/hiddenEye.png";
// });

// // 🧾 Redirect to Register Page
// document.getElementById("goRegister").addEventListener("click", function () {
//   window.location.href = "register.html";
// });

// // 🔐 Login Form Handler
// document
//   .getElementById("loginForm")
//   .addEventListener("submit", async function (event) {
//     event.preventDefault();

//     const email = document.getElementById("email").value.trim();
//     const password = passwordInput.value.trim();
//     const errorElem = document.getElementById("error");
//     errorElem.textContent = "";

//     if (!email || !password) {
//       errorElem.textContent = "Please enter both username and password.";
//       return;
//     }

//     try {
//       const response = await fetch("../backend/login.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({ email, password }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         // ✅ Redirect based on role
//         switch (result.role) {
//           case "owner":
//             window.location.href = "flowershopOwner.php";
//             // window.location.href = "createShop.php";
//             break;
//           case "customer":
//             // window.location.href = "home.php"; // SEE YOU ON BETA
//             // window.location.href = "anniversary.php";
//             window.location.href = "home.php";
//             break;
//           case "admin":
//             window.location.href = "admin_dashboard.php";
//             break;
//           default:
//             errorElem.textContent = "Unrecognized user role.";
//         }
//       } else {
//         errorElem.textContent = result.message || "Login failed. Try again.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Please try later.";
//     }
//   });
