const API_BASE_URL = "/api/v1";

function getToken() {
    return localStorage.getItem("access_token");
}

// Đồng bộ Sanctum token vào cookie để Laravel bảo vệ cả request mở trang Blade.
function setWebAuthCookie(token) {
    if (!token) return;
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = "petclinic_token=" + encodeURIComponent(token)
        + "; Path=/; Max-Age=28800; SameSite=Lax" + secure;
}

function clearWebAuthCookie() {
    document.cookie = "petclinic_token=; Path=/; Max-Age=0; SameSite=Lax";
}

function getCurrentUser() {
    const raw = localStorage.getItem("current_user");
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch (e) {
        localStorage.removeItem("current_user");
        return null;
    }
}

function checkAuth() {
    if (!getToken()) {
        window.location.href = "/login";
    }
}

async function apiRequest(endpoint, options = {}) {
    const token = getToken();
    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: "Bearer " + token } : {}),
        ...(options.headers || {}),
    };

    const response = await fetch(API_BASE_URL + endpoint, {
        ...options,
        headers,
    });

    if (response.status === 401) {
        localStorage.removeItem("access_token");
        localStorage.removeItem("current_user");
        clearWebAuthCookie();
        window.location.href = "/login";
        throw new Error("Phiên đăng nhập hết hạn hoặc không hợp lệ");
    }

    const result = await response.json().catch(() => ({}));
    if (!response.ok) {
        if (result.code === "PASSWORD_CHANGE_REQUIRED") {
            if (window.location.pathname !== "/change-password") {
                window.location.href = "/change-password";
            }
            throw new Error(result.message);
        }
        if (result.errors) {
            if (typeof window.showFormValidationErrors === "function") {
                window.showFormValidationErrors(result.errors);
            }
            const firstError = Object.values(result.errors).flat()[0];
            throw new Error(firstError || result.message);
        }
        throw new Error(result.message || `Lỗi máy chủ (${response.status})`);
    }
    return result;
}

async function logout() {
    try {
        await apiRequest("/auth/logout", { method: "POST" });
    } finally {
        localStorage.removeItem("access_token");
        localStorage.removeItem("current_user");
        clearWebAuthCookie();
        window.location.href = "/login";
    }
}

function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isHidden = input.type === "password";
    input.type = isHidden ? "text" : "password";

    const icon = button.querySelector("i");
    if (icon) {
        icon.classList.toggle("fa-eye", !isHidden);
        icon.classList.toggle("fa-eye-slash", isHidden);
    }

    const label = isHidden ? "Ẩn mật khẩu" : "Hiện mật khẩu";
    button.setAttribute("aria-label", label);
    button.setAttribute("title", label);
}

function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString("vi-VN") + " đ";
}

async function initPage() {
    checkAuth();
    const roleMap = {
        QUAN_TRI: "Quản trị viên",
        BAC_SI: "Bác sĩ thú y",
        NHAN_VIEN: "Nhân viên / Lễ tân",
    };

    let user = getCurrentUser();
    if (!user) {
        try {
            const res = await apiRequest("/users/me");
            user = res.data || res;
            if (user && (user.ho_ten || user.username)) {
                localStorage.setItem("current_user", JSON.stringify(user));
            }
        } catch (e) {
            return;
        }
    }

    if (user && user.phai_doimatkhau) {
        window.location.href = "/change-password";
        return;
    }

    const nameEl = document.getElementById("navUserName");
    const roleEl = document.getElementById("navUserRole");
    if (nameEl) {
        nameEl.innerText = (user && (user.ho_ten || user.username)) || "Tài khoản";
    }
    if (roleEl && user && user.vaitro) {
        roleEl.innerText = roleMap[user.vaitro] || user.vaitro;
    }
    const adminNav = document.getElementById('adminUsersNav');
    if (adminNav && user && user.vaitro === 'QUAN_TRI') {
        adminNav.classList.remove('d-none');
    }
}


// Phân trang dùng chung cho các bảng danh sách trong hệ thống: tối đa 5 dòng/trang.
(function initTablePagination() {
    const PAGE_SIZE = 5;
    const tableStates = new WeakMap();
    let refreshQueued = false;

    function buildPageNumbers(current, total) {
        if (total <= 7) return Array.from({ length: total }, (_, index) => index + 1);
        const pages = new Set([1, total, current, current - 1, current + 1]);
        const sorted = [...pages].filter(page => page >= 1 && page <= total).sort((a, b) => a - b);
        const result = [];
        sorted.forEach((page, index) => {
            if (index && page - sorted[index - 1] > 1) result.push("...");
            result.push(page);
        });
        return result;
    }

    function renderPagination(tbody, rows, state, controls) {
        const totalPages = Math.ceil(rows.length / PAGE_SIZE);
        state.page = Math.min(Math.max(state.page, 1), totalPages);
        rows.forEach((row, index) => {
            row.hidden = index < (state.page - 1) * PAGE_SIZE || index >= state.page * PAGE_SIZE;
        });

        controls.replaceChildren();
        if (totalPages <= 1) {
            controls.classList.add("d-none");
            return;
        }
        controls.classList.remove("d-none");

        const info = document.createElement("span");
        info.className = "small text-muted mr-2";
        info.textContent = "Trang " + state.page + "/" + totalPages + " · " + rows.length + " dòng";
        controls.appendChild(info);

        const addButton = (label, page, disabled = false, active = false) => {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-sm " + (active ? "btn-primary" : "btn-outline-primary") + " mx-1";
            button.textContent = label;
            button.disabled = disabled;
            button.setAttribute("aria-label", "Trang " + page);
            button.addEventListener("click", () => {
                state.page = page;
                renderPagination(tbody, rows, state, controls);
            });
            controls.appendChild(button);
        };

        addButton("‹", state.page - 1, state.page === 1);
        buildPageNumbers(state.page, totalPages).forEach(page => {
            if (page === "...") {
                const dots = document.createElement("span");
                dots.className = "mx-1 text-muted";
                dots.textContent = "...";
                controls.appendChild(dots);
            } else {
                addButton(String(page), page, false, page === state.page);
            }
        });
        addButton("›", state.page + 1, state.page === totalPages);
    }

    function paginateTable(tbody) {
        if (!tbody || tbody.closest("[data-no-pagination]")) return;
        const rows = Array.from(tbody.children).filter(row => row.tagName === "TR");
        let state = tableStates.get(tbody);
        if (!state) {
            const controls = document.createElement("div");
            controls.className = "pet-pagination d-flex flex-wrap align-items-center justify-content-end mt-2";
            const table = tbody.closest("table");
            const wrapper = table?.closest(".table-responsive") || table;
            if (!wrapper || !wrapper.parentNode) return;
            wrapper.insertAdjacentElement("afterend", controls);
            state = { rows: [], page: 1, controls };
            tableStates.set(tbody, state);
        }

        const changed = rows.length !== state.rows.length || rows.some((row, index) => row !== state.rows[index]);
        if (changed) {
            state.rows = rows;
            state.page = 1;
        }
        renderPagination(tbody, rows, state, state.controls);
    }

    function refreshTables() {
        refreshQueued = false;
        document.querySelectorAll("table tbody").forEach(paginateTable);
    }

    function scheduleRefresh() {
        if (refreshQueued) return;
        refreshQueued = true;
        window.setTimeout(refreshTables, 0);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", refreshTables, { once: true });
    } else {
        refreshTables();
    }

    const observer = new MutationObserver(mutations => {
        const needsRefresh = mutations.some(mutation => {
            if (mutation.target?.nodeType === 1 && mutation.target.closest("tbody")) return true;
            return Array.from(mutation.addedNodes || []).some(node =>
                node.nodeType === 1 && (node.matches?.("table") || node.querySelector?.("table"))
            );
        });
        if (needsRefresh) scheduleRefresh();
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
