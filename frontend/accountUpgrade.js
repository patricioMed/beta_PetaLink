document
  .getElementById("registerForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    // Collect values
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
    const shopName = shopNameField ? shopNameField.value.trim() : "";

    // Elements for feedback
    const errorElem = document.getElementById("error");
    const successElem = document.getElementById("success");
    errorElem.textContent = "";
    successElem.textContent = "";

    // Basic validation
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

    if (password !== confirmPassword) {
      errorElem.textContent = "Passwords do not match.";
      return;
    }

    // Password complexity
    const passwordPattern =
      /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).{8,}$/;
    if (!passwordPattern.test(password)) {
      errorElem.textContent =
        "Password must be at least 8 characters with a number, uppercase, lowercase, and symbol.";
      return;
    }

    // If upgrading to owner, shop name is required
    if (role === "owner" && !shopName) {
      errorElem.textContent = "Shop name is required for owners.";
      return;
    }

    try {
      // Always send shop_name (use null if not provided)
      const response = await fetch("../backend/accountUpgrade.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name,
          contact_number: contact,
          email,
          password,
          role,
          address,
          shop_name: role === "owner" ? shopName : null,
        }),
      });

      const result = await response.json();

      if (result.success) {
        successElem.textContent = "Registration successful!";
        document.getElementById("registerForm").reset();

        // Redirect after short delay
        setTimeout(() => {
          window.location.href = "accountProcess.php";
        }, 2000);
      } else {
        errorElem.textContent = result.message || "Registration failed.";
      }
    } catch (error) {
      errorElem.textContent = "Server error. Please try again.";
    }
  });

// Show/hide shop name field dynamically
document.getElementById("role").addEventListener("change", function () {
  const shopContainer = document.getElementById("shopNameContainer");
  shopContainer.style.display = this.value === "owner" ? "block" : "none";
});

////////////////////////////////////////////////////////////////////////////////////////////////
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
//     const shopNameField = document.getElementById("shopName");
//     const shopName = shopNameField && shopNameField.value.trim();

//     const errorElem = document.getElementById("error");
//     const successElem = document.getElementById("success");
//     errorElem.textContent = "";
//     successElem.textContent = "";

//     if (
//       !name ||
//       !contact ||
//       !email ||
//       !password ||
//       !confirmPassword ||
//       !role ||
//       !address
//     ) {
//       errorElem.textContent = "Please fill out all fields.";
//       return;
//     }

//     if (role === "owner" && !shopName) {
//       errorElem.textContent = "Shop name is required for owners.";
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
//       const response = await fetch("../backend/accountUpgrade.php", {
//         // const response = await fetch("../backend/registerLocation.php", {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify({
//           name,
//           contact_number: contact,
//           email,
//           password,
//           role,
//           address,
//           shop_name: role === "owner" ? shopName : "",
//         }),
//       });

//       const result = await response.json();

//       if (result.success) {
//         successElem.textContent = "Registration successful!";
//         document.getElementById("registerForm").reset();

//         setTimeout(() => {
//           window.location.href = "accountProcess.php";
//         }, 3000);
//       } else {
//         errorElem.textContent = result.message || "Registration failed.";
//       }
//     } catch (error) {
//       errorElem.textContent = "Server error. Try again.";
//     }
//   });

// // Show/hide shop name field dynamically
// document.getElementById("role").addEventListener("change", function () {
//   const shopContainer = document.getElementById("shopNameContainer");
//   shopContainer.style.display = this.value === "owner" ? "block" : "none";
// });
