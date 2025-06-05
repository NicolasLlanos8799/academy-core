<?php
// 🔒 Prevent browser caching when navigating "back"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 💬 Show message only if session expired
$message = '';
if (isset($_GET['expirada'])) {
    $message = '⚠️ Your session has expired due to inactivity. Please log in again.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Name Academy</title>

    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="login-body">

    <div class="login-container">
        <h2>Log In</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-warning text-center mt-3"><?= $message ?></div>
        <?php endif; ?>

        <form id="formLogin">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required placeholder="Email address">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Password">

            <button type="submit">Log In</button>
        </form>

        <p class="text-center mt-3">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modalReset">Forgot your password?</a>
        </p>

        <p id="error-message" style="color: red; margin-top: 10px;"></p>
    </div>

    <!-- 🔐 Password Reset Modal -->
    <div class="modal fade" id="modalReset" tabindex="-1" aria-labelledby="resetLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formReset">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetLabel">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="email" name="email" class="form-control mb-3" placeholder="Your email" required>
                        <input type="password" name="nueva_contrasena" class="form-control"
                            placeholder="New password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ Success Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body p-4">
                    <h5 class="mb-3" id="alertModalLabel">✅ Password updated</h5>
                    <p>Your password has been successfully changed.</p>
                    <button type="button" class="btn btn-primary mt-2" data-bs-dismiss="modal"
                        id="btnCerrarAlerta">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Prevent showing old form when navigating back -->
    <script>
        const entries = performance.getEntriesByType("navigation");
        if (entries.length && entries[0].type === "back_forward") {
            window.location.reload();
        }

        window.onload = function () {
            const form = document.getElementById("formLogin");
            if (form) {
                form.reset();
                form.email.value = "";
                form.password.value = "";
            }
        };
    </script>

    <!-- Bootstrap and scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>

</html>
