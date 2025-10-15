// document
//   .getElementById("registerForm")
//   .addEventListener("submit", async function (event) {
//     event.preventDefault();

//     const username = document.getElementById("username").value.trim();
//     const password = document.getElementById("password").value.trim();
//     const confirmPassword = document
//       .getElementById("confirmPassword")
//       .value.trim();

//     const errorElem = document.getElementById("error");
//     const successElem = document.getElementById("success");

//     errorElem.textContent = "";
//     successElem.textContent = "";

//     if (!username || !password || !confirmPassword) {
//       errorElem.textContent = "Please fill out all fields.";
//       return;
//     }

//     if (password !== confirmPassword) {
//       errorElem.textContent = "Passwords do not match.";
//       return;
//     }

//     try {
//       const response = await fetch("../backend/register.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({ username, password }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         // successElem.textContent = "Registration successful!";
//         successElem.textContent = "Registration successful1!";
//         setTimeout(() => {
//           successElem.textContent = "";
//           window.location.href = "login.html";
//         }, 5000);
//         document.getElementById("registerForm").reset();

//         // Create and show the "Go to Login" button
//         const loginButton = document.createElement("button");
//         loginButton.textContent = "Go to Login";
//         loginButton.style.marginTop = "10px";
//         loginButton.style.padding = "8px 16px";
//         loginButton.style.backgroundColor = "#4CAF50";
//         loginButton.style.color = "white";
//         loginButton.style.border = "none";
//         loginButton.style.borderRadius = "4px";
//         loginButton.style.cursor = "pointer";

//         // Add a line break and then the button
//         successElem.appendChild(document.createElement("br"));
//         successElem.appendChild(loginButton);

//         // Redirect on click
//         // loginButton.addEventListener("click", () => {
//         //   window.location.href = "login.html"; // update path if needed
//         // });
//       } else {
//         errorElem.textContent = result.message || "Registration failed.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Try again.";
//     }
//   });
// document
//   .getElementById("registerForm")
//   .addEventListener("submit", async function (event) {
//     event.preventDefault();

//     const name = document.getElementById("name").value.trim();
//     const contact = document.getElementById("contact").value.trim();
//     const address = document.getElementById("address").value.trim();
//     const email = document.getElementById("email").value.trim();
//     const password = document.getElementById("password").value.trim();
//     const confirmPassword = document
//       .getElementById("confirmPassword")
//       .value.trim();
//     const role = document.getElementById("role").value;

//     const errorElem = document.getElementById("error");
//     const successElem = document.getElementById("success");
//     errorElem.textContent = "";
//     successElem.textContent = "";

//     if (!name || !contact || !email || !password || !confirmPassword || !role) {
//       errorElem.textContent = "Please fill out all fields.";
//       return;
//     }

//     if (password !== confirmPassword) {
//       errorElem.textContent = "Passwords do not match.";
//       return;
//     }

//     const passwordPattern =
//       /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).{8,}$/;
//     if (!passwordPattern.test(password)) {
//       errorElem.textContent =
//         "Password must be at least 8 characters with a number, uppercase, lowercase, and symbol.";
//       return;
//     }

//     try {
//       const response = await fetch("../backend/register.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({
//           name,
//           contact_number: contact,
//           email,
//           password,
//           role,
//           address,
//         }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         successElem.textContent = "Registration successful!";
//         document.getElementById("registerForm").reset();

//         setTimeout(() => {
//           window.location.href = "login.html";
//         }, 3000);
//       } else {
//         errorElem.textContent = result.message || "Registration failed.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Try again.";
//     }
//   });
// document
//   .getElementById("registerForm")
//   .addEventListener("submit", async function (event) {
//     event.preventDefault();

//     const name = document.getElementById("name").value.trim();
//     const contact = document.getElementById("contact").value.trim();
//     const address = document.getElementById("address").value.trim();
//     const email = document.getElementById("email").value.trim();
//     const password = document.getElementById("password").value.trim();
//     const confirmPassword = document
//       .getElementById("confirmPassword")
//       .value.trim();
//     const role = document.getElementById("role").value;

//     const errorElem = document.getElementById("error");
//     const successElem = document.getElementById("success");
//     errorElem.textContent = "";
//     successElem.textContent = "";

//     if (!name || !contact || !email || !password || !confirmPassword || !role) {
//       errorElem.textContent = "Please fill out all fields.";
//       return;
//     }

//     if (password !== confirmPassword) {
//       errorElem.textContent = "Passwords do not match.";
//       return;
//     }

//     const passwordPattern =
//       /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).{8,}$/;
//     if (!passwordPattern.test(password)) {
//       errorElem.textContent =
//         "Password must be at least 8 characters with a number, uppercase, lowercase, and symbol.";
//       return;
//     }

//     try {
//       const response = await fetch("../backend/register.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({
//           name,
//           contact_number: contact,
//           email,
//           password,
//           role,
//           address,
//         }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         successElem.textContent = "Registration successful!";
//         document.getElementById("registerForm").reset();

//         setTimeout(() => {
//           window.location.href = "login.html";
//         }, 3000);
//       } else {
//         errorElem.textContent = result.message || "Registration failed.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Try again.";
//     }
//   });

document
  .getElementById("registerForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value.trim();
    const contact = document.getElementById("contact").value.trim();
    const address = document.getElementById("address").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document
      .getElementById("confirmPassword")
      .value.trim();
    const role = document.getElementById("role").value;
    const shopNameField = document.getElementById("shopName");
    const shopName = shopNameField && shopNameField.value.trim();

    const errorElem = document.getElementById("error");
    const successElem = document.getElementById("success");
    errorElem.textContent = "";
    successElem.textContent = "";

    if (
      !name ||
      !contact ||
      !email ||
      !password ||
      !confirmPassword ||
      !role ||
      !address
    ) {
      errorElem.textContent = "Please fill out all fields.";
      return;
    }

    if (role === "owner" && !shopName) {
      errorElem.textContent = "Shop name is required for owners.";
      return;
    }

    if (password !== confirmPassword) {
      errorElem.textContent = "Passwords do not match.";
      return;
    }

    const passwordPattern =
      /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).{8,}$/;
    if (!passwordPattern.test(password)) {
      errorElem.textContent =
        "Password must be at least 8 characters with a number, uppercase, lowercase, and symbol.";
      return;
    }

    try {
      const response = await fetch("../backend/register18.php", {
        // const response = await fetch("../backend/registerLocation.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name,
          contact_number: contact,
          email,
          password,
          role,
          address,
          shop_name: role === "owner" ? shopName : "",
        }),
      });

      const result = await response.json();

      if (result.success) {
        successElem.textContent = "Registration successful!";
        document.getElementById("registerForm").reset();

        setTimeout(() => {
          window.location.href = "login.html";
        }, 3000);
      } else {
        errorElem.textContent = result.message || "Registration failed.";
      }
    } catch (error) {
      errorElem.textContent = "Server error. Try again.";
    }
  });

// Show/hide shop name field dynamically
document.getElementById("role").addEventListener("change", function () {
  const shopContainer = document.getElementById("shopNameContainer");
  shopContainer.style.display = this.value === "owner" ? "block" : "none";
});
