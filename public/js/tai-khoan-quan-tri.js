document.addEventListener("DOMContentLoaded", async () => {
    const user = getCurrentUser();
    if (!user || user.vaitro !== "QUAN_TRI") {
        window.location.href = "/dashboard";
        return;
    }
    await initPage();
    const table = document.getElementById("usersTable");
    const alertBox = document.getElementById("usersAlert");
    const notify = (message, ok = false) => {
        alertBox.textContent = message;
        alertBox.className = "alert " + (ok ? "alert-success" : "alert-danger");
    };
    const escapeHtml = (value) => String(value ?? "").replace(/[&<>"']/g, ch => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    })[ch]);
    const statusLabels = {
        HOAT_DONG: "Hoạt động",
        KHOA: "Đã khóa",
        TAM_NGUNG: "Tạm ngừng"
    };

    async function loadUsers() {
        try {
            const result = await apiRequest("/admin/users");
            const users = result.data || [];
            table.innerHTML = users.length ? users.map(item => {
                const active = item.trangthai === "HOAT_DONG";
                const isAdmin = item.vaitro === "QUAN_TRI";
                const role = { QUAN_TRI: "Quản trị viên", BAC_SI: "Bác sĩ", NHAN_VIEN: "Nhân viên" }[item.vaitro] || item.vaitro;
                const actions = isAdmin
                    ? '<span class="text-muted">Tài khoản quản trị hệ thống</span>'
                    : `<button class="btn btn-sm btn-outline-primary mb-1" data-action="edit" data-id="${item.user_id}" data-user="${encodeURIComponent(JSON.stringify(item))}">Sửa</button>                        <button class="btn btn-sm btn-outline-danger mb-1" data-action="delete" data-id="${item.user_id}">Xóa</button>                        <button class="btn btn-sm btn-outline-warning mb-1" data-action="reset" data-id="${item.user_id}">Cấp lại MK</button>
                       <div class="input-group input-group-sm mt-1" style="min-width: 190px;">
                           <select class="form-control" id="status-${item.user_id}" aria-label="Trạng thái tài khoản">
                               <option value="HOAT_DONG" ${item.trangthai === "HOAT_DONG" ? "selected" : ""}>Hoạt động</option>
                               <option value="TAM_NGUNG" ${item.trangthai === "TAM_NGUNG" ? "selected" : ""}>Tạm ngừng hoạt động</option>
                               <option value="KHOA" ${item.trangthai === "KHOA" ? "selected" : ""}>Đã khóa</option>
                           </select>
                           <div class="input-group-append">
                               <button class="btn btn-outline-primary" data-action="status" data-id="${item.user_id}">Lưu</button>
                           </div>
                       </div>`;
                return `<tr>
                    <td>${item.user_id}</td><td>${escapeHtml(item.username)}</td>
                    <td>${escapeHtml(item.ho_ten)}</td><td>${escapeHtml(item.email)}</td>
                    <td>${escapeHtml(role)}</td><td>${escapeHtml(statusLabels[item.trangthai] || item.trangthai)}</td>
                    <td class="text-nowrap">${actions}</td></tr>`;
            }).join("") : '<tr><td colspan="7" class="text-center">Chưa có tài khoản.</td></tr>';
        } catch (error) {
            notify(error.message);
            table.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Không tải được dữ liệu.</td></tr>';
        }
    }

    table.addEventListener("click", async (event) => {
        const button = event.target.closest("button[data-action]");
        if (!button) return;
        const id = button.dataset.id;
        try {
            if (button.dataset.action === "edit") {
                const item = JSON.parse(decodeURIComponent(button.dataset.user));
                document.getElementById("editUserId").value = item.user_id;
                document.getElementById("editUsername").value = item.username || "";
                document.getElementById("editName").value = item.ho_ten || "";
                document.getElementById("editEmail").value = item.email || "";
                document.getElementById("editPhone").value = item.phone || "";
                document.getElementById("editRole").value = item.vaitro || "NHAN_VIEN";
                $("#editUserModal").modal("show");
            } else if (button.dataset.action === "delete") {
                const username = button.closest("tr")?.children[1]?.textContent || ("#" + id);
                if (!confirm("Xóa tài khoản " + username + "? Thao tác này không thể hoàn tác.")) return;
                await apiRequest("/admin/users/" + id, { method: "DELETE" });
                notify("Đã xóa tài khoản thành công.", true);
                await loadUsers();
            } else if (button.dataset.action === "reset") {
                document.getElementById("resetUserId").value = id;
                document.getElementById("resetPasswordAlert").classList.add("d-none");
                $("#resetPasswordModal").modal("show");
            } else {
                const status = document.getElementById(`status-${id}`).value;
                const confirmMessage = status === "KHOA"
                    ? "Bạn chắc chắn muốn khóa tài khoản này?"
                    : status === "TAM_NGUNG"
                        ? "Bạn chắc chắn muốn tạm ngừng hoạt động tài khoản này?"
                        : "Mở lại hoạt động cho tài khoản này?";
                if (!confirm(confirmMessage)) return;
                await apiRequest(`/admin/users/${id}/status`, {
                    method: "PUT", body: JSON.stringify({ trangthai: status })
                });
                notify("Cập nhật trạng thái tài khoản thành công.", true);
                await loadUsers();
            }
        } catch (error) { notify(error.message); }
    });

    document.getElementById("editUserForm").addEventListener("submit", async (event) => {
        event.preventDefault();
        const id = document.getElementById("editUserId").value;
        try {
            await apiRequest("/admin/users/" + id, {
                method: "PUT",
                body: JSON.stringify({
                    username: document.getElementById("editUsername").value.trim(),
                    ho_ten: document.getElementById("editName").value.trim(),
                    email: document.getElementById("editEmail").value.trim(),
                    phone: document.getElementById("editPhone").value.trim(),
                    vaitro: document.getElementById("editRole").value
                })
            });
            $("#editUserModal").modal("hide");
            notify("Cập nhật tài khoản thành công.", true);
            await loadUsers();
        } catch (error) { notify(error.message); }
    });
    document.getElementById("resetPasswordForm").addEventListener("submit", async (event) => {
        event.preventDefault();
        const id = document.getElementById("resetUserId").value;
        const resetAlert = document.getElementById("resetPasswordAlert");
        try {
            const result = await apiRequest(`/admin/users/${id}/reset-password`, {
                method: "PUT"
            });
            const generatedPassword = result.data?.mat_khau_tam || "";
            resetAlert.textContent = "Mật khẩu tạm mới: " + generatedPassword + ". Hãy sao chép và gửi bảo mật cho người dùng; mật khẩu chỉ hiển thị lần này.";
            resetAlert.className = "alert alert-success";
            notify("Đã cấp lại mật khẩu. Người dùng sẽ phải đổi mật khẩu khi đăng nhập.", true);
            await loadUsers();
        } catch (error) {
            resetAlert.textContent = error.message;
            resetAlert.className = "alert alert-danger";
        }
    });

    const createForm = document.getElementById("createUserForm");
    const createAlert = document.getElementById("createUserAlert");
    const createFields = {
        username: document.getElementById("newUsername"),
        ho_ten: document.getElementById("newName"),
        email: document.getElementById("newEmail"),
        phone: document.getElementById("newPhone")
    };
    function clearCreateErrors() {
        Object.values(createFields).forEach(input => {
            input.classList.remove("is-invalid");
            input.nextElementSibling?.classList.contains("form-text") && input.nextElementSibling.nextElementSibling?.classList.contains("invalid-feedback")
                ? input.nextElementSibling.nextElementSibling.textContent = ""
                : input.parentElement.querySelector(".invalid-feedback") && (input.parentElement.querySelector(".invalid-feedback").textContent = "");
        });
        createAlert.className = "alert d-none";
        createAlert.textContent = "";
    }
    function showCreateError(field, message) {
        const input = createFields[field];
        if (!input) return;
        input.classList.add("is-invalid");
        const feedback = input.parentElement.querySelector(".invalid-feedback");
        if (feedback) feedback.textContent = message;
    }
    Object.entries(createFields).forEach(([field, input]) => {
        input.addEventListener("input", () => {
            input.classList.remove("is-invalid");
            const feedback = input.parentElement.querySelector(".invalid-feedback");
            if (feedback) feedback.textContent = "";
        });
    });

    createForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        clearCreateErrors();
        const username = createFields.username.value.trim();
        const name = createFields.ho_ten.value.trim();
        const email = createFields.email.value.trim();
        const phone = createFields.phone.value.trim();
        let invalid = false;
        if (!username) { showCreateError("username", "Vui lòng nhập tên đăng nhập."); invalid = true; }
        else if (username.length > 50) { showCreateError("username", "Tên đăng nhập không được vượt quá 50 ký tự."); invalid = true; }
        if (!name) { showCreateError("ho_ten", "Vui lòng nhập họ tên."); invalid = true; }
        else if (name.length > 100) { showCreateError("ho_ten", "Họ tên không được vượt quá 100 ký tự."); invalid = true; }
        if (!email) { showCreateError("email", "Vui lòng nhập email."); invalid = true; }
        else if (!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)) { showCreateError("email", "Email không đúng định dạng."); invalid = true; }
        else if (email.length > 100) { showCreateError("email", "Email không được vượt quá 100 ký tự."); invalid = true; }
        if (phone.length > 20) { showCreateError("phone", "Số điện thoại không được vượt quá 20 ký tự."); invalid = true; }
        if (invalid) {
            createAlert.textContent = "Vui lòng kiểm tra và sửa các thông tin được đánh dấu.";
            createAlert.className = "alert alert-danger";
            return;
        }
        try {
            const result = await apiRequest("/admin/users", {
                method: "POST",
                body: JSON.stringify({
                    username: document.getElementById("newUsername").value.trim(),
                    ho_ten: document.getElementById("newName").value.trim(),
                    email: document.getElementById("newEmail").value.trim(),
                    phone: document.getElementById("newPhone").value.trim(),
                    vaitro: document.getElementById("newRole").value
                })
            });
            $("#createUserModal").modal("hide");
            event.target.reset();
            const generatedPassword = result.data?.mat_khau_tam || "";
            notify("Tạo tài khoản thành công. Mật khẩu tạm: " + generatedPassword + ". Hãy sao chép và gửi bảo mật cho người dùng; mật khẩu chỉ hiển thị lần này.", true);
            await loadUsers();
        } catch (error) {
            const message = error.message || "Không thể tạo tài khoản.";
            const lower = message.toLowerCase();
            let field = null;
            if (lower.includes("username") || lower.includes("tên đăng nhập")) field = "username";
            else if (lower.includes("email")) field = "email";
            else if (lower.includes("phone") || lower.includes("điện thoại")) field = "phone";
            else if (lower.includes("ho_ten") || lower.includes("họ tên")) field = "ho_ten";
            if (field) showCreateError(field, message);
            createAlert.textContent = message;
            createAlert.className = "alert alert-danger";
        }
    });
    await loadUsers();
});
