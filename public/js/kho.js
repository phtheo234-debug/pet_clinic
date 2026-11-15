let inventoryItems = [];
let isAdminInventory = false;
let isAdminSpa = false;

document.addEventListener("DOMContentLoaded", async () => {
    await initPage();
    const user = getCurrentUser();
    isAdminInventory = !!user && user.vaitro === "QUAN_TRI";
    isAdminSpa = isAdminInventory;

    if (isAdminInventory) {
        document.getElementById("btnAddSpaService")?.classList.remove("d-none");
        document.getElementById("spaActionHeader")?.classList.remove("d-none");
        document.querySelectorAll(".inventoryAddButton, .inventoryActionHeader").forEach(el => el.classList.remove("d-none"));
    }

    document.getElementById("inventoryForm")?.addEventListener("submit", saveInventoryItem);
    document.getElementById("spaServiceForm")?.addEventListener("submit", saveSpaService);
    document.getElementById("inventory_category")?.addEventListener("change", () => {
        if (!document.getElementById("inventory_form_id").value) {
            document.getElementById("inventory_code").value = generateInventoryItemCode(document.getElementById("inventory_category").value);
        }
        toggleInventoryFields();
    });
    document.querySelectorAll("[data-search-inventory]").forEach(button =>
        button.addEventListener("click", () => searchInventoryTable(button.dataset.searchInventory))
    );
    document.querySelectorAll(".inventory-search input").forEach(input =>
        input.addEventListener("keydown", event => {
            if (event.key === "Enter") searchInventoryTable(input.closest(".inventory-search").dataset.inventorySearch);
        })
    );
    toggleInventoryFields();
    await loadInventory();
});

async function loadInventory() {
    try {
        const res = await apiRequest("/inventory");
        inventoryItems = Array.isArray(res) ? res : (Array.isArray(res.data) ? res.data : []);
        renderInventory(inventoryItems);
    } catch (err) {
        ["medicalInventoryTableBody", "vaccineInventoryTableBody", "foodInventoryTableBody", "goodsInventoryTableBody"].forEach(id => {
            const body = document.getElementById(id);
            if (body) body.innerHTML = `<tr><td colspan="8" class="text-danger text-center">${escapeInventoryHtml(err.message || "Lỗi tải kho")}</td></tr>`;
        });
    }
}

function escapeInventoryHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, char => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
    })[char]);
}

function renderInventory(items) {
    const renderTable = (rows, bodyId) => {
        const body = document.getElementById(bodyId);
        if (!body) return;
        body.innerHTML = rows.length ? rows.map(item => {
            const quantity = Number(item.soluongton ?? item.tong_ton_kho ?? 0);
            const status = quantity > 0
                ? '<span class="badge badge-success">Còn hàng</span>'
                : '<span class="badge badge-secondary">Hết hàng</span>';
            const actions = isAdminInventory
                ? '<td class="text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editInventoryItem(' + Number(item.item_id) + ')">Sửa</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInventoryItem(' + Number(item.item_id) + ')">Xóa</button></td>'
                : '';
            return '<tr><td><code>' + escapeInventoryHtml(item.masanpham) + '</code></td>' +
                '<td><strong>' + escapeInventoryHtml(item.ten_sanpham) + '</strong></td>' +
                '<td>' + quantity + '</td><td>' + formatCurrency(item.dongia) + '</td><td>' + status + '</td>' + actions + '</tr>';
        }).join("") : '<tr><td colspan="' + (isAdminInventory ? 6 : 5) + '" class="text-center text-muted">Chưa có mặt hàng.</td></tr>';
    };

    renderTable(items.filter(item => item.danhmuc === "THUOC"), "medicalInventoryTableBody");
    renderTable(items.filter(item => item.danhmuc === "VAC_XIN"), "vaccineInventoryTableBody");
    renderTable(items.filter(item => item.danhmuc === "THUC_AN"), "foodInventoryTableBody");

    const goods = items.filter(item => item.danhmuc === "DO_DUNG");
    const goodsBody = document.getElementById("goodsInventoryTableBody");
    if (goodsBody) goodsBody.innerHTML = goods.length ? goods.map(item => {
        const actions = isAdminInventory ? '<td class="text-nowrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editInventoryItem(' + Number(item.item_id) + ')">Sửa</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInventoryItem(' + Number(item.item_id) + ')">Xóa</button></td>' : '';
        return '<tr><td><code>' + escapeInventoryHtml(item.masanpham) + '</code></td>' +
            '<td><strong>' + escapeInventoryHtml(item.ten_sanpham) + '</strong></td>' +
            '<td>' + Number(item.soluongton || 0) + '</td><td>' + formatCurrency(item.dongia) + '</td>' + actions + '</tr>';
    }).join("") : '<tr><td colspan="' + (isAdminInventory ? 5 : 4) + '" class="text-center text-muted">Chưa có đồ dùng.</td></tr>';

    const services = items.filter(item => item.danhmuc === "DICH_VU_SPA");
    const spaBody = document.getElementById("spaTableBody");
    if (spaBody) spaBody.innerHTML = services.length ? services.map(service => {
        const actions = isAdminSpa ? '<td class="text-nowrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editSpaService(' + Number(service.item_id) + ')">Sửa</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSpaService(' + Number(service.item_id) + ')">Xóa</button></td>' : '';
        return '<tr><td><code>' + escapeInventoryHtml(service.masanpham) + '</code></td>' +
            '<td><strong>' + escapeInventoryHtml(service.ten_sanpham) + '</strong></td>' +
            '<td class="text-primary font-weight-bold">' + formatCurrency(service.dongia) + '</td>' + actions + '</tr>';
    }).join("") : '<tr><td colspan="' + (isAdminSpa ? 4 : 3) + '" class="text-center text-muted">Chưa có dịch vụ.</td></tr>';
}

function localInventoryDate() {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

function generateInventoryItemCode(category) {
    const prefixes = {THUOC:"THUOC", VAC_XIN:"VACXIN", THUC_AN:"THUCAN", DO_DUNG:"DODUNG"};
    const prefix = prefixes[category] || "SP";
    const date = localInventoryDate().replaceAll("-", "");
    const suffix = String(Math.floor(Math.random() * 1000000)).padStart(6, "0");
    return prefix + "-" + date + "-" + suffix;
}

function generateSpaServiceCode() {
    const date = localInventoryDate().replaceAll("-", "");
    const suffix = String(Math.floor(Math.random() * 1000000)).padStart(6, "0");
    return "SPA-" + date + "-" + suffix;
}

function toggleInventoryFields() {
    const category = document.getElementById("inventory_category")?.value;
    const isMedical = ["THUOC", "VAC_XIN"].includes(category);
    const isGoods = category === "DO_DUNG";
    const categoryGroup = document.getElementById("inventoryCategoryGroup");
    const unitGroup = document.getElementById("inventoryUnitGroup");
    const unit = document.getElementById("inventory_unit");
    if (categoryGroup) categoryGroup.classList.toggle("d-none", !isMedical);
    if (unitGroup) unitGroup.classList.toggle("d-none", isGoods);
    if (unit) unit.required = !isGoods;
    const help = document.getElementById("inventoryStockHelp");
    if (help) help.textContent = "Số lượng tồn được quản lý trực tiếp tại mặt hàng, không chia theo lô.";
}

function openInventoryForm(item = null, category = null) {
    if (!isAdminInventory) return;
    document.getElementById("inventoryForm").reset();
    document.getElementById("inventory_form_id").value = item ? item.item_id : "";
    document.getElementById("inventoryFormTitle").innerText = item ? "Cập nhật mặt hàng kho" : "Thêm mặt hàng kho";
    document.getElementById("inventory_category").value = item?.danhmuc || category || "THUOC";
    document.getElementById("inventory_code").value = item?.masanpham || generateInventoryItemCode(document.getElementById("inventory_category").value);
    document.getElementById("inventory_name").value = item?.ten_sanpham || "";
    document.getElementById("inventory_unit").value = item?.donvitinh || "";
    document.getElementById("inventory_price").value = item ? (Number(item.dongia || 0) / 1000) : "";
    document.getElementById("inventory_stock").value = item ? Number(item.soluongton || 0) : 0;
    toggleInventoryFields();
    $("#inventoryFormModal").modal("show");
}

function editInventoryItem(itemId) {
    if (!isAdminInventory) return;
    const item = inventoryItems.find(row => Number(row.item_id) === Number(itemId));
    if (!item) { alert("Không tìm thấy mặt hàng."); return; }
    openInventoryForm(item);
}

async function saveInventoryItem(event) {
    event.preventDefault();
    if (!isAdminInventory) return alert("Chỉ quản trị viên được quản lý danh mục kho.");
    const id = document.getElementById("inventory_form_id").value;
    const category = document.getElementById("inventory_category").value;
    const payload = {
        masanpham: document.getElementById("inventory_code").value,
        ten_sanpham: document.getElementById("inventory_name").value.trim(),
        danhmuc: category,
        donvitinh: category === "DO_DUNG" ? "" : document.getElementById("inventory_unit").value.trim(),
        soluong: Number(document.getElementById("inventory_stock").value || 0),
        dongia: Number(document.getElementById("inventory_price").value) * 1000,
    };

    const button = document.getElementById("btnSaveInventory");
    button.disabled = true;
    button.innerText = "Đang lưu...";
    try {
        await apiRequest(id ? `/admin/inventory/${id}` : "/admin/inventory", {
            method: id ? "PUT" : "POST",
            body: JSON.stringify(payload),
        });
        $("#inventoryFormModal").modal("hide");
        await loadInventory();
        alert(id ? "Đã cập nhật mặt hàng." : "Đã thêm mặt hàng vào kho.");
    } catch (error) {
        alert("Không thể lưu mặt hàng: " + error.message);
    } finally {
        button.disabled = false;
        button.innerText = "Lưu mặt hàng";
    }
}

async function deleteInventoryItem(itemId) {
    if (!isAdminInventory) return;
    const item = inventoryItems.find(row => Number(row.item_id) === Number(itemId));
    if (!item || !confirm(`Bạn có chắc muốn xóa "${item.masanpham} - ${item.ten_sanpham}"?`)) return;
    try {
        await apiRequest(`/admin/inventory/${itemId}`, {method:"DELETE"});
        await loadInventory();
        alert("Đã xóa mặt hàng.");
    } catch (error) {
        alert("Không thể xóa mặt hàng: " + error.message);
    }
}

function openSpaServiceForm(service = null) {
    if (!isAdminSpa) return;
    document.getElementById("spaServiceForm").reset();
    document.getElementById("spa_service_form_id").value = service ? service.item_id : "";
    document.getElementById("spaServiceFormTitle").innerText = service ? "Cập nhật dịch vụ Spa" : "Thêm dịch vụ Spa";
    document.getElementById("spa_service_code").value = service?.masanpham || generateSpaServiceCode();
    document.getElementById("spa_service_name").value = service?.ten_sanpham || "";
    document.getElementById("spa_service_price").value = service ? Number(service.dongia || 0) / 1000 : "";
    $("#spaServiceFormModal").modal("show");
}

function editSpaService(serviceId) {
    if (!isAdminSpa) return;
    const service = inventoryItems.find(item => Number(item.item_id) === Number(serviceId) && item.danhmuc === "DICH_VU_SPA");
    if (!service) return alert("Không tìm thấy dịch vụ Spa.");
    openSpaServiceForm(service);
}

async function saveSpaService(event) {
    event.preventDefault();
    if (!isAdminSpa) return alert("Chỉ quản trị viên được quản lý dịch vụ Spa.");
    const id = document.getElementById("spa_service_form_id").value;
    const payload = {
        masanpham: document.getElementById("spa_service_code").value,
        ten_sanpham: document.getElementById("spa_service_name").value.trim(),
        dongia: Number(document.getElementById("spa_service_price").value) * 1000,
    };
    const button = document.getElementById("btnSaveSpaService");
    button.disabled = true;
    button.innerText = "Đang lưu...";
    try {
        await apiRequest(id ? `/admin/spa-services/${id}` : "/admin/spa-services", {
            method: id ? "PUT" : "POST", body: JSON.stringify(payload),
        });
        $("#spaServiceFormModal").modal("hide");
        await loadInventory();
        alert(id ? "Đã cập nhật dịch vụ Spa." : "Đã thêm dịch vụ Spa.");
    } catch (error) {
        alert("Không thể lưu dịch vụ Spa: " + error.message);
    } finally {
        button.disabled = false;
        button.innerText = "Lưu dịch vụ";
    }
}

async function deleteSpaService(serviceId) {
    if (!isAdminSpa) return;
    const service = inventoryItems.find(item => Number(item.item_id) === Number(serviceId) && item.danhmuc === "DICH_VU_SPA");
    if (!service || !confirm(`Bạn có chắc muốn xóa dịch vụ "${service.masanpham} - ${service.ten_sanpham}"?`)) return;
    try {
        await apiRequest(`/admin/spa-services/${serviceId}`, {method:"DELETE"});
        await loadInventory();
        alert("Đã xóa dịch vụ Spa.");
    } catch (error) {
        alert("Không thể xóa dịch vụ Spa: " + error.message);
    }
}

function searchInventoryTable(type) {
    const input = document.getElementById("searchInventory_" + type);
    const keyword = String(input?.value || "").trim().toLocaleLowerCase("vi");
    const config = {medical:["medicalInventoryTableBody"],vaccine:["vaccineInventoryTableBody"],food:["foodInventoryTableBody"],goods:["goodsInventoryTableBody"],spa:["spaTableBody"]};
    (config[type] || []).forEach(id => {
        const body = document.getElementById(id);
        if (!body) return;
        body.querySelectorAll("tr").forEach(row => {
            row.classList.toggle("d-none", !!keyword && !row.textContent.toLocaleLowerCase("vi").includes(keyword));
        });
    });
}
