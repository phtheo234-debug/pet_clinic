document.addEventListener("DOMContentLoaded", async () => {
    const loginForm = document.getElementById("loginForm");
    const alertBox = document.getElementById("loginAlert");
    const btnSubmit = document.getElementById("btnSubmitLogin");

    // Kiểm tra token cũ trước khi chuyển trang.
    // Nếu chỉ dựa vào localStorage, token hết hạn sẽ tạo vòng lặp:
    // /login -> /dashboard -> middleware trả về /login.
    const token = getToken();
    if (token) {
        try {
            const response = await fetch(API_BASE_URL + "/users/me", {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    Authorization: "Bearer " + token,
                },
            });

            if (response.ok) {
                const result = await response.json();
                const user = result.data || result;

                localStorage.setItem("current_user", JSON.stringify({
                    user_id: user.user_id,
                    username: user.username,
                    ho_ten: user.ho_ten,
                    vaitro: user.vaitro,
                    phai_doimatkhau: user.phai_doimatkhau,
                }));
                setWebAuthCookie(token);

                window.location.replace(user.phai_doimatkhau
                    ? "/change-password"
                    : "/dashboard");
                return;
            }

            // Token không còn hợp lệ: xóa cả localStorage và cookie
            // để trang đăng nhập không tự chuyển hướng lặp lại.
            if (response.status === 401 || response.status === 403) {
                localStorage.removeItem("access_token");
                localStorage.removeItem("current_user");
                clearWebAuthCookie();
            } else {
                throw new Error("Không thể kiểm tra phiên đăng nhập. Vui lòng thử lại.");
            }
        } catch (error) {
            // Lỗi mạng không đồng nghĩa token hết hạn; không tự xóa token.
            if (alertBox) {
                alertBox.textContent = error.message || "Không thể kết nối máy chủ.";
                alertBox.classList.remove("d-none");
            }
        }
    }

    if (!loginForm) return;

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (alertBox) alertBox.classList.add("d-none");
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML =
                '<i class="fas fa-spinner fa-spin mr-1"></i> Đang xác thực...';
        }

        const username = document.getElementById("username").value.trim();
        const mat_khau = document.getElementById("mat_khau").value;

        try {
            const res = await fetch(API_BASE_URL + "/auth/login", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ username, mat_khau }),
            });

            const result = await res.json();
            if (!res.ok) {
                throw new Error(result.message || "Sai tên đăng nhập hoặc mật khẩu");
            }

            const payload = result.data || result;
            localStorage.setItem("access_token", payload.token);
            setWebAuthCookie(payload.token);
            localStorage.setItem("current_user", JSON.stringify({
                user_id: payload.user_id,
                username: payload.username,
                ho_ten: payload.ho_ten,
                vaitro: payload.vaitro,
                phai_doimatkhau: payload.phai_doimatkhau,
            }));

            window.location.replace(payload.phai_doimatkhau
                ? "/change-password"
                : "/dashboard");
        } catch (err) {
            if (alertBox) {
                alertBox.textContent = err.message;
                alertBox.classList.remove("d-none");
            }
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML =
                    '<i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập hệ thống';
            }
        }
    });
});
