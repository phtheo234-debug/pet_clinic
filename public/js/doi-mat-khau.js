document.addEventListener("DOMContentLoaded", () => {
    if (!getToken()) {
        window.location.href = "/login";
        return;
    }

    const form = document.getElementById("changePasswordForm");
    const alertBox = document.getElementById("changePasswordAlert");
    const button = document.getElementById("btnChangePassword");

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        alertBox.classList.add("d-none");

        const currentPassword = document.getElementById("mat_khau_cu").value;
        const newPassword = document.getElementById("mat_khau_moi").value;
        const confirmation = document.getElementById("mat_khau_moi_confirmation").value;

        const passwordPattern = /^(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S{6,10}$/;
        const username = (getCurrentUser() || {}).username || "";
        if (!passwordPattern.test(newPassword) || newPassword !== confirmation || newPassword === currentPassword || newPassword.toLowerCase() === username.toLowerCase()) {
            alertBox.textContent = !passwordPattern.test(newPassword)
                ? "Mật khẩu phải dài 6–10 ký tự, không có khoảng trắng và gồm chữ, số, ký tự đặc biệt."
                : newPassword.toLowerCase() === username.toLowerCase()
                    ? "Mật khẩu không được trùng tên đăng nhập."
                    : newPassword !== confirmation
                        ? "Mật khẩu mới nhập lại không khớp."
                        : "Mật khẩu mới phải khác mật khẩu hiện tại.";
            alertBox.classList.remove("d-none");
            const target = !passwordPattern.test(newPassword) || newPassword.toLowerCase() === username.toLowerCase()
                ? document.getElementById("mat_khau_moi")
                : document.getElementById("mat_khau_moi_confirmation");
            if (typeof window.setFormFieldError === "function") window.setFormFieldError(target, alertBox.textContent);
            return;
        }

        button.disabled = true;
        button.textContent = "Đang cập nhật...";

        try {
            await apiRequest("/users/change-password", {
                method: "PUT",
                body: JSON.stringify({
                    mat_khau_cu: currentPassword,
                    mat_khau_moi: newPassword,
                    mat_khau_moi_confirmation: confirmation,
                }),
            });

            const user = getCurrentUser() || {};
            user.phai_doimatkhau = false;
            localStorage.setItem("current_user", JSON.stringify(user));
            window.location.href = "/dashboard";
        } catch (error) {
            alertBox.textContent = error.message || "Không thể đổi mật khẩu.";
            alertBox.classList.remove("d-none");
            if (typeof window.setFormFieldError === "function") {
                const target = /hiện tại/i.test(alertBox.textContent)
                    ? document.getElementById("mat_khau_cu")
                    : document.getElementById("mat_khau_moi");
                window.setFormFieldError(target, alertBox.textContent);
            }
        } finally {
            button.disabled = false;
            button.textContent = "Xác nhận đổi mật khẩu";
        }
    });
});
