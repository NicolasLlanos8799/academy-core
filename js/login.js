// =========================================================
// File: login.js
// Functionality: Login form handling
// =========================================================

document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("formLogin");

    loginForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;
        const errorMessage = document.getElementById("error-message");

        fetch("php/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.id_profesor) {
                    localStorage.setItem("id_profesor", data.id_profesor);
                }
                window.location.href = data.redirect;
            } else {
                errorMessage.textContent = data.message;
            }
        })
        .catch(error => {
            errorMessage.textContent = "Server connection error.";
            console.error("Error:", error);
        });
    });

    // =========================================================
    // Password reset via modal
    // =========================================================
    const formReset = document.getElementById("formReset");
    if (formReset) {
        formReset.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formReset);

            fetch("php/reset_password.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modalReset = bootstrap.Modal.getInstance(document.getElementById("modalReset"));
                    if (modalReset) modalReset.hide();

                    formReset.reset();

                    const modalAlerta = new bootstrap.Modal(document.getElementById("alertModal"));
                    modalAlerta.show();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                alert("An error occurred while trying to reset the password.");
                console.error(err);
            });
        });
    }
});
