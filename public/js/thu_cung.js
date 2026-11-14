let petsList = [];

let currentNguoiDungRole = "";

function isDoctor() {
    return currentNguoiDungRole === "BAC_SI";
}

// Tự động sinh mã thú cưng hiển thị tạm trên form
function generateNextThuCungCode() {
    if (!petsList.length) {
        return "PET-001";
    }

    let maxNum = 0;

    petsList.forEach((p) => {
        const match = (p.mathucung || "").match(/\d+/);

        if (match) {
            const num = parseInt(match[0], 10);

            if (num > maxNum) {
                maxNum = num;
            }
        }
    });

    return "PET-" + String(maxNum + 1).padStart(3, "0");
}

// Chuẩn hóa giới tính
function normalizeGender(raw) {
    const val = (raw || "").trim().toUpperCase();

    if (val === "DUC" || val === "ĐỰC" || val === "MALE") return "DUC";
    if (val === "CAI" || val === "CÁI" || val === "FEMALE") return "CAI";

    return "CHUA_XAC_DINH";
}

function extractList(res) {
    if (Array.isArray(res)) return res;
    if (res && Array.isArray(res.data)) return res.data;
    if (res && res.data && Array.isArray(res.data.data)) return res.data.data;

    return [];
}

document.addEventListener("DOMContentLoaded", async () => {
    await initPage();

    const currentNguoiDung = getCurrentNguoiDung();

    currentNguoiDungRole = String(currentNguoiDung?.vaitro || "")
        .trim()
        .toUpperCase();

    // Tải danh sách sau khi xác định vai trò để hiển thị đúng quyền cập nhật.
    await loadThuCungs();
    await loadChuNuois();

    const canReceiveThuCungs = ["QUAN_TRI", "NHAN_VIEN"].includes(currentNguoiDungRole);

    const addThuCungButton = document.getElementById("btnOpenAddThuCung");

    if (addThuCungButton && canReceiveThuCungs) {
        addThuCungButton.classList.remove("d-none");
    }
    $("#vaccinationScheduleModal").on("show.bs.modal", loadVaccinationSchedule);


    // Tự động điền mã thú cưng khi mở modal tiếp nhận
    $("#addThuCungModal").on("show.bs.modal", function () {
        const codeInput = document.getElementById("mathucung");

        if (codeInput) {
            codeInput.value = generateNextThuCungCode();
        }
    });

    // Tìm kiếm bằng nút Tìm hoặc phím Enter.
    const runThuCungSearch = () => {
        const kw = document.getElementById("searchThuCung").value.trim().toLocaleLowerCase("vi");

        const filtered = petsList.filter((p) => {
            const petName = (p.name || p.ten_thucung || "").toLowerCase();

            const ownerName = (
                (p.owner && (p.owner.name || p.owner.ho_ten)) ||
                p.ho_ten_chu ||
                ""
            ).toLowerCase();

            const ownerPhone = (p.owner && p.owner.phone) || p.phone || "";

            return (
                (p.mathucung && p.mathucung.toLowerCase().includes(kw)) ||
                petName.includes(kw) ||
                ownerName.includes(kw) ||
                ownerPhone.includes(kw)
            );
        });

        renderThuCungsTable(filtered);
    };
    document.getElementById("btnSearchThuCung")?.addEventListener("click", runThuCungSearch);
    document.getElementById("searchThuCung")?.addEventListener("keydown", event => { if (event.key === "Enter") runThuCungSearch(); });

    document.getElementById("petsTableBody").addEventListener("click", async event => {
        const editButton = event.target.closest("[data-edit-pet]");
        if (editButton) { openEditThuCung(Number(editButton.dataset.editThuCung)); return; }
        const deleteButton = event.target.closest("[data-delete-pet]");
        if (deleteButton) {
            const id = Number(deleteButton.dataset.deleteThuCung);
            const pet = petsList.find(item => Number(item.pet_id ?? item.id) === id);
            if (!confirm("Xóa hồ sơ thú cưng " + (pet?.name || pet?.ten_thucung || ("#" + id)) + "? Chỉ xóa được hồ sơ chưa phát sinh dữ liệu khám/hóa đơn.")) return;
            try { await apiRequest("/pets/" + id, { method: "DELETE" }); await loadThuCungs(); alert("Đã xóa hồ sơ thú cưng."); }
            catch (error) { alert("Không thể xóa hồ sơ: " + error.message); }
        }
    });

    document.getElementById("editThuCungForm")?.addEventListener("submit", async event => {
        event.preventDefault();
        const petId = Number(document.getElementById("editThuCungId").value);
        const ownerId = Number(document.getElementById("editThuCungChuNuoiSelect").value);
        if (!ownerId) { alert("Vui lòng chọn chủ nuôi."); return; }
        try {
            await apiRequest("/pets/" + petId, { method: "PUT", body: JSON.stringify({ owner_id: ownerId, name: document.getElementById("editThuCungName").value.trim(), species: document.getElementById("editThuCungSpecies").value, breed: document.getElementById("editThuCungBreed").value.trim() || null, gender: document.getElementById("editThuCungGender").value, dob: document.getElementById("editThuCungDob").value || null }) });
            $("#editThuCungModal").modal("hide");
            await Promise.all([loadThuCungs(), loadChuNuois()]);
            alert("Đã cập nhật hồ sơ thú cưng và chủ nuôi liên kết.");
        } catch (error) { alert("Không thể cập nhật: " + error.message); }
    });

    document.getElementById("editChuNuoiForm")?.addEventListener("submit", async event => {
        event.preventDefault(); const id = Number(document.getElementById("editChuNuoiId").value);
        try { await apiRequest("/owners/" + id, { method: "PUT", body: JSON.stringify({ ho_ten: document.getElementById("editChuNuoiName").value.trim(), phone: document.getElementById("editChuNuoiPhone").value.trim(), email: document.getElementById("editChuNuoiEmail").value.trim(), diachi: document.getElementById("editChuNuoiAddress").value.trim() }) });
            $("#editChuNuoiModal").modal("hide"); await Promise.all([loadChuNuois(), loadThuCungs()]); alert("Đã cập nhật chủ nuôi.");
        } catch (error) { alert("Không thể cập nhật chủ nuôi: " + error.message); }
    });

    document.getElementById("ownersTableBody")?.addEventListener("click", async event => {
        const edit = event.target.closest("[data-edit-owner]"); const remove = event.target.closest("[data-delete-owner]");
        if (edit) { const owner = JSON.parse(decodeURIComponent(edit.dataset.owner));
            document.getElementById("editChuNuoiId").value = owner.owner_id; document.getElementById("editChuNuoiName").value = owner.ho_ten || "";
            document.getElementById("editChuNuoiPhone").value = owner.phone || ""; document.getElementById("editChuNuoiEmail").value = owner.email || "";
            document.getElementById("editChuNuoiAddress").value = owner.diachi || ""; $("#editChuNuoiModal").modal("show");
        } else if (remove) { const id = Number(remove.dataset.deleteChuNuoi);
            if (!confirm("Xóa chủ nuôi này? Hệ thống chỉ cho phép xóa khi không còn thú cưng liên kết.")) return;
            try { await apiRequest("/owners/" + id, { method: "DELETE" }); await loadChuNuois(); alert("Đã xóa chủ nuôi."); }
            catch (error) { alert("Không thể xóa chủ nuôi: " + error.message); }
        }
    });
    // Mở hộp thoại ghi chú khi yêu cầu đưa thú cưng vào danh sách chờ.
    document.getElementById("petsTableBody").addEventListener("click", event => {
        const button = event.target.closest("[data-add-medical-queue]");
        if (!button) return;
        const petId = Number(button.dataset.petId || 0);
        const pet = petsList.find(item => Number(item.pet_id ?? item.id) === petId);
        document.getElementById("queueThuCungId").value = String(petId);
        document.getElementById("queueThuCungName").textContent =
            (pet?.mathucung ? pet.mathucung + " · " : "") + (pet?.ten_thucung || pet?.name || "Thú cưng");
        document.getElementById("queueThuCungNote").value = button.dataset.queueNote || "";
        $("#addMedicalQueueModal").modal("show");
    });

    // Xác nhận gửi vào hàng đợi; ghi chú là tùy chọn.
    document.getElementById("addMedicalQueueForm")?.addEventListener("submit", async event => {
        event.preventDefault();
        const petId = Number(document.getElementById("queueThuCungId").value || 0);
        const note = document.getElementById("queueThuCungNote").value.trim();
        const button = document.getElementById("btnConfirmAddMedicalQueue");
        if (!petId) {
            alert("Không xác định được thú cưng cần đưa vào chờ khám.");
            return;
        }
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Đang gửi...';
        try {
            const result = await apiRequest("/medical/queue", {
                method: "POST",
                body: JSON.stringify({ pet_id: petId, ghi_chu: note })
            });
            $("#addMedicalQueueModal").modal("hide");
            document.getElementById("addMedicalQueueForm").reset();
            await loadThuCungs();
            alert(result?.message || "Đã đưa thú cưng vào danh sách chờ khám.");
        } catch (error) {
            alert("Không thể đưa thú cưng vào chờ khám: " + error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-check mr-1"></i>Xác nhận';
        }
    });

    // Tiếp nhận hồ sơ chủ nuôi và thú cưng
    document.getElementById("petForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!canReceiveThuCungs) {
            alert("Chỉ nhân viên hoặc quản trị viên được tiếp nhận thú cưng.");
            return;
        }

        const ownerName = document.getElementById("ho_ten_chu").value.trim();
        const ownerPhone = document.getElementById("phone").value.trim();
        const ownerEmail = document.getElementById("owner_email").value.trim();
        const ownerAddress = document.getElementById("owner_address").value.trim();
        const petName = document.getElementById("ten_thucung").value.trim();
        const species = document.getElementById("loai").value;
        const breed = document.getElementById("giong").value.trim() || null;
        const dob = document.getElementById("ngaysinh").value || null;

        const gender = normalizeGender(
            document.getElementById("gioitinh").value,
        );

        const weight = parseFloat(document.getElementById("can_nang").value);

        if (!ownerName || !ownerPhone) {
            alert("Vui lòng nhập đầy đủ Họ tên và Số điện thoại chủ nuôi!");
            return;
        }

        if (!petName) {
            alert("Vui lòng nhập tên thú cưng!");
            return;
        }

        if (isNaN(weight) || weight <= 0) {
            alert("Cân nặng thú cưng phải lớn hơn 0 kg!");
            document.getElementById("can_nang").focus();
            return;
        }

        const btnSave = document.getElementById("btnSaveThuCung");

        btnSave.disabled = true;
        btnSave.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-1"></i> Đang lưu...';

        try {
            let ownerId = null;

            try {
                const ownersRes = await apiRequest("/owners");
                const owners = extractList(ownersRes);

                const existingChuNuoi = owners.find(
                    (o) => (o.phone || "").trim() === ownerPhone,
                );

                if (existingChuNuoi) {
                    ownerId = existingChuNuoi.owner_id ?? existingChuNuoi.id;
                }
            } catch (_) {}

            const payload = {
                owner_id: ownerId,
                ho_ten: ownerName,
                phone: ownerPhone,
                email: ownerEmail || null,
                diachi: ownerAddress || null,
                name: petName,
                species: species,
                breed: breed,
                gender: gender,
                dob: dob,
                weight: weight,
            };

            await apiRequest("/pets", {
                method: "POST",
                body: JSON.stringify(payload),
            });

            e.target.reset();

            $("#addThuCungModal").modal("hide");

            await loadThuCungs();

            alert("Tiếp nhận hồ sơ chủ nuôi & thú cưng thành công!");
        } catch (err) {
            alert("Lỗi thêm hồ sơ thú cưng: " + err.message);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save mr-1"></i> Lưu Hồ sơ';
        }
    });
});

// Quản lý thông tin chủ nuôi.
async function loadChuNuois() {
    const body = document.getElementById("ownersTableBody"); if (!body) return;
    try { const owners = extractList(await apiRequest("/owners"));
        const canManageChuNuois = ["QUAN_TRI", "NHAN_VIEN"].includes(currentNguoiDungRole);
        body.innerHTML = owners.length ? owners.map(owner => { const encoded = encodeURIComponent(JSON.stringify(owner));
            const editButton = canManageChuNuois ? `<button type="button" class="btn btn-sm btn-outline-primary" data-edit-owner data-owner="${encoded}">Sửa</button>` : '<span class="text-muted">Chỉ xem</span>'; const deleteButton = currentNguoiDungRole === "QUAN_TRI" ? ` <button type="button" class="btn btn-sm btn-outline-danger" data-delete-owner="${Number(owner.owner_id)}" ${Number(owner.pet_count || 0) > 0 ? "disabled" : ""}>Xóa</button>` : ""; const actions = editButton + deleteButton;
            return `<tr><td>${escapeThuCungHtml(owner.ho_ten)}</td><td>${escapeThuCungHtml(owner.phone)}</td><td>${escapeThuCungHtml(owner.email || "-")}</td><td>${escapeThuCungHtml(owner.diachi || "-")}</td><td class="text-nowrap">${actions} <small class="text-muted ml-1">${Number(owner.pet_count || 0)} thú cưng</small></td></tr>`;
        }).join("") : '<tr><td colspan="5" class="text-center text-muted">Chưa có chủ nuôi.</td></tr>';
    } catch (error) { body.innerHTML = `<tr><td colspan="5" class="text-danger text-center">${escapeThuCungHtml(error.message)}</td></tr>`; }
}

async function openEditThuCung(petId) {
    try { const response = await apiRequest("/pets/" + petId); const pet = response?.data?.data || response?.data || {};
        document.getElementById("editThuCungId").value = petId;
        const owners = extractList(await apiRequest("/owners"));
        const ownerSelect = document.getElementById("editThuCungChuNuoiSelect");
        ownerSelect.innerHTML = owners.map(owner => `<option value="${Number(owner.owner_id)}">${escapeThuCungHtml(owner.ho_ten)} · ${escapeThuCungHtml(owner.phone)}</option>`).join("");
        ownerSelect.value = String(pet.owner_id || pet.owner?.id || "");
        document.getElementById("editThuCungName").value = pet.name || ""; document.getElementById("editThuCungSpecies").value = pet.species || "Khác";
        document.getElementById("editThuCungBreed").value = pet.breed || ""; document.getElementById("editThuCungGender").value = pet.gender || "CHUA_XAC_DINH";
        document.getElementById("editThuCungDob").value = pet.dob || ""; $("#editThuCungModal").modal("show");
    } catch (error) { alert("Không tải được hồ sơ cần sửa: " + error.message); }
}
// Tải danh sách thú cưng
async function loadThuCungs() {
    try {
        const res = await apiRequest("/pets");

        petsList = extractList(res);

        renderThuCungsTable(petsList);
    } catch (err) {
        document.getElementById("petsTableBody").innerHTML =
            `<tr><td colspan="7" class="text-danger text-center">${escapeThuCungHtml(err.message)}</td></tr>`;
    }
}

// Hiển thị danh sách thú cưng
function renderThuCungsTable(items) {
    const statusBadges = {
        NOI_TRU: '<span class="badge badge-warning">Nội trú</span>',
        NGOAI_TRU: '<span class="badge badge-success">Ngoại trú</span>',
        DA_XUAT_VIEN: '<span class="badge badge-secondary">Đã xuất viện</span>',
        CHUYEN_VIEN: '<span class="badge badge-danger">Chuyển viện</span>',
    };

    const tbody = document.getElementById("petsTableBody");

    if (!items.length) {
        tbody.innerHTML =
            '<tr><td colspan="7" class="text-center text-muted">Chưa có dữ liệu thú cưng</td></tr>';
        return;
    }

    tbody.innerHTML = items
        .map((p) => {
            const petId = p.id ?? p.pet_id;

            const petName = p.name || p.ten_thucung || "-";
            const species = p.species || p.loai || "";
            const breed = p.breed || p.giong || "";

            const ownerName =
                (p.owner && (p.owner.name || p.owner.ho_ten)) ||
                p.ho_ten_chu ||
                "-";

            const ownerPhone = (p.owner && p.owner.phone) || p.phone || "";

            const weight =
                p.weight ??
                p.can_nang ??
                (p.latest_weight && p.latest_weight.can_nang) ??
                "-";

            return `
            <tr>
                <td class="font-weight-bold text-primary">
                    ${escapeThuCungHtml(p.mathucung || "-")}
                </td>

                <td>
                    <strong>${escapeThuCungHtml(petName)}</strong>
                    (${escapeThuCungHtml(species)}${breed ? " - " + escapeThuCungHtml(breed) : ""})
                </td>

                <td>
                    ${escapeThuCungHtml(ownerName)}
                    <br>
                    <small class="text-muted">
                        ${escapeThuCungHtml(ownerPhone)}
                    </small>
                </td>

                <td>
                    <strong>
                        ${
                            weight !== "-" && weight !== null && weight !== ""
                                ? escapeThuCungHtml(weight) + " kg"
                                : "-"
                        }
                    </strong>
                </td>

                <td>
                    ${statusBadges[p.trangthai] || escapeThuCungHtml(p.trangthai || "Ngoại trú")}
                </td>
                <td>
                    ${p.trangthai_hangdoi ? '<span class="badge ' + (p.trangthai_hangdoi === "DANG_KHAM" ? "badge-warning" : p.trangthai_hangdoi === "DA_KHAM" ? "badge-success" : "badge-secondary") + '">' + ({CHO_KHAM:"Chưa khám",DANG_KHAM:"Đang khám",DA_KHAM:"Đã khám"})[p.trangthai_hangdoi] + '</span>' : p.trangthai_kham ? '<span class="badge badge-info">' + escapeThuCungHtml(({CHUA_KHAM:"Chưa khám",DANG_DIEU_TRI:"Đang điều trị",HOAN_THANH:"Hoàn thành",CHUYEN_VIEN:"Chuyển viện"})[p.trangthai_kham] || p.trangthai_kham) + '</span>' : '<span class="text-muted">Chưa khám</span>'}
                    ${p.kham_da_thanh_toan ? '<br><small class="text-success">Đã thanh toán</small>' : ''}
                    ${p.ngaykham_gannhat ? '<br><small class="text-muted">' + formatThuCungDate(p.ngaykham_gannhat) + '</small>' : ''}

                </td>

                <td>
                    ${["QUAN_TRI", "NHAN_VIEN"].includes(currentNguoiDungRole) && !p.trangthai_hangdoi && (!p.trangthai_kham || p.trangthai_kham === "CHUA_KHAM") ? '<button type="button" class="btn btn-sm btn-outline-danger mr-1" data-add-medical-queue data-pet-id="' + Number(petId) + '" data-queue-note="' + escapeThuCungHtml(p.ghi_chu_cho_kham || "") + '"><i class="fas fa-user-clock mr-1"></i>Đưa vào chờ khám</button>' : p.trangthai_hangdoi === "CHO_KHAM" ? '<span class="badge badge-secondary mr-1">Đang chờ khám</span>' : p.trangthai_hangdoi === "DANG_KHAM" ? '<span class="badge badge-warning mr-1">Đang khám</span>' : ""}
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="openThuCungDetail(${Number(petId)})"><i class="fas fa-eye"></i> Chi tiết</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="printThuCungProfile(${Number(petId)})"><i class="fas fa-print"></i> In hồ sơ</button>
                    ${["QUAN_TRI", "NHAN_VIEN"].includes(currentNguoiDungRole) ? '<button type="button" class="btn btn-sm btn-outline-warning ml-1" data-edit-pet="' + Number(petId) + '"><i class="fas fa-edit"></i> Sửa</button> <button type="button" class="btn btn-sm btn-outline-danger ml-1" data-delete-pet="' + Number(petId) + '"><i class="fas fa-trash"></i> Xóa</button>' : ""}
                </td>
            </tr>
        `;
        })
        .join("");
}

// Mã hóa nội dung trước khi hiển thị HTML
function escapeThuCungHtml(value) {
    return String(value ?? "").replace(
        /[&<>"']/g,
        (char) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            })[char],
    );
}

// Định dạng ngày giờ
function formatThuCungDate(value) {
    if (!value) return "-";

    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? escapeThuCungHtml(value)
        : date.toLocaleString("vi-VN");
}

// Xem chi tiết thú cưng, cập nhật cân nặng và trạng thái phiếu khám gần nhất
async function openThuCungDetail(petId) {
    const content = document.getElementById("petDetailContent");
    content.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Đang tải thông tin...</div>';
    $("#petDetailModal").modal("show");

    try {
        const response = await apiRequest(`/pets/${petId}`);
        const pet = response?.data?.data || response?.data || response;
        const history = Array.isArray(pet.medical_history) ? pet.medical_history : [];
        const exam = pet.latest_exam || null;

        const examStatusLabels = {
            CHUA_KHAM: "Chưa khám",
            DANG_DIEU_TRI: "Đang điều trị",
            HOAN_THANH: "Hoàn thành",
            CHUYEN_VIEN: "Chuyển viện",
        };
        const historyRows = history.length ? history.map((item) => `
            <tr><td>${escapeThuCungHtml(item.record_id)}</td><td>${formatThuCungDate(item.ngaykham)}</td><td>${escapeThuCungHtml(item.chandoan || "-")}</td><td>${escapeThuCungHtml(item.ten_phauthuat || item.huongxuly || "-")}</td><td>${escapeThuCungHtml(examStatusLabels[item.trangthai] || item.trangthai || "-")}</td><td>${escapeThuCungHtml(item.mahoadon || (item.invoice_id ? "Đã thanh toán" : "Chưa thanh toán"))}</td></tr>
        `).join("") : '<tr><td colspan="6" class="text-center text-muted">Chưa có lịch sử khám bệnh</td></tr>';

        const examStatusControl = exam
            ? `<div class="mt-2"><strong>Trạng thái khám gần nhất:</strong> ${escapeThuCungHtml(examStatusLabels[exam.trangthai] || exam.trangthai || "-")}<br><small class="text-muted">Phiếu khám #${escapeThuCungHtml(exam.record_id)} · ${formatThuCungDate(exam.ngaykham)}</small></div>`
            : '<div class="text-muted mt-2">Thú cưng chưa có phiếu khám.</div>';

        content.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-6 mb-3"><strong>Mã thú cưng:</strong><br>${escapeThuCungHtml(pet.mathucung || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Tên:</strong><br>${escapeThuCungHtml(pet.name || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Loài / Giống:</strong><br>${escapeThuCungHtml(pet.species || "-")} / ${escapeThuCungHtml(pet.breed || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Ngày sinh:</strong><br>${escapeThuCungHtml(pet.dob || "Chưa cập nhật")}</div>
                <div class="col-md-6 mb-3"><strong>Chủ nuôi:</strong><br>${escapeThuCungHtml(pet.owner?.name || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Số điện thoại:</strong><br>${escapeThuCungHtml(pet.owner?.phone || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Trạng thái lưu trú:</strong><br>${escapeThuCungHtml(pet.trangthai || "-")}</div>
            </div>

            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary"><i class="fas fa-weight mr-1"></i>Cân nặng hiện tại</h6>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-7 mb-2">
                            <label for="detailThuCungWeight">Cân nặng (kg)</label>
                            <input type="number" id="detailThuCungWeight" class="form-control" min="0.1" max="999" step="0.1" value="${pet.weight ?? ""}" placeholder="Nhập cân nặng">
                        </div>
                        <div class="form-group col-md-5 mb-2">
                            <button type="button" id="btnUpdateThuCungWeight" class="btn btn-primary btn-block">Lưu cân nặng</button>
                        </div>
                    </div>
                    <small class="text-muted">Mỗi lần cập nhật sẽ được ghi thành một bản ghi mới trong lịch sử cân nặng.</small>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="font-weight-bold text-primary"><i class="fas fa-stethoscope mr-1"></i>Trạng thái khám</h6>
                ${examStatusControl}
            </div>

            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-history mr-1"></i>Lịch sử khám bệnh</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light"><tr><th>Mã phiếu</th><th>Ngày khám</th><th>Chẩn đoán</th><th>Hướng xử lý</th><th>Trạng thái</th><th>Hóa đơn</th></tr></thead>
                    <tbody>${historyRows}</tbody>
                </table>
            </div>
        `;

        document.getElementById("btnUpdateThuCungWeight").addEventListener("click", async () => {
            const input = document.getElementById("detailThuCungWeight");
            const weight = Number(input.value);
            if (!Number.isFinite(weight) || weight <= 0 || weight > 999) {
                alert("Vui lòng nhập cân nặng lớn hơn 0 và không vượt quá 999 kg.");
                input.focus();
                return;
            }
            const button = document.getElementById("btnUpdateThuCungWeight");
            button.disabled = true;
            try {
                await apiRequest(`/pets/${petId}`, {
                    method: "PUT",
                    body: JSON.stringify({ weight }),
                });
                await loadThuCungs();
                alert("Đã cập nhật cân nặng.");
                await openThuCungDetail(petId);
            } catch (err) {
                alert("Không cập nhật được cân nặng: " + err.message);
                button.disabled = false;
            }
        });


    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger mb-0">Không tải được chi tiết thú cưng: ${escapeThuCungHtml(err.message)}</div>`;
    }
}


/** In hồ sơ thú cưng và lịch sử lưu trú. */
async function printThuCungProfile(petId) {
    try {
        const response = await apiRequest("/pets/" + petId);
        const pet = response?.data?.data || response?.data || {};
        const history = Array.isArray(pet.medical_history) ? pet.medical_history : [];
        const historyRows = history.map((item, index) =>
            "<tr><td>" + (index + 1) + "</td><td>#" + escapeThuCungHtml(item.record_id) + "</td><td>" + formatThuCungDate(item.ngaykham) +
            "</td><td>" + escapeThuCungHtml(item.chandoan || "-") + "</td><td>" + escapeThuCungHtml(item.trangthai || "-") +
            "</td><td>" + escapeThuCungHtml(item.mahoadon || (item.invoice_id ? "Đã thanh toán" : "Chưa thanh toán")) + "</td></tr>"
        ).join("");
        const w = window.open("", "_blank", "width=900,height=750");
        if (!w) { alert("Trình duyệt đang chặn cửa sổ in. Hãy cho phép popup rồi thử lại."); return; }
        w.document.write("<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Hồ sơ thú cưng</title><style>body{font-family:Arial;padding:28px;color:#222}h2,h3{text-align:center}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:24px 0}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border:1px solid #777;padding:8px;text-align:left}</style></head><body>" +
            "<h2>PET CLINIC</h2><h3>HỒ SƠ THÚ CƯNG</h3><div class='grid'>" +
            "<div><b>Mã thú cưng:</b> " + escapeThuCungHtml(pet.mathucung || "-") + "</div><div><b>Tên:</b> " + escapeThuCungHtml(pet.name || "-") + "</div>" +
            "<div><b>Loài:</b> " + escapeThuCungHtml(pet.species || "-") + "</div><div><b>Giống:</b> " + escapeThuCungHtml(pet.breed || "-") + "</div>" +
            "<div><b>Giới tính:</b> " + escapeThuCungHtml(pet.gender || "-") + "</div><div><b>Ngày sinh:</b> " + escapeThuCungHtml(pet.dob || "-") + "</div>" +
            "<div><b>Cân nặng hiện tại:</b> " + escapeThuCungHtml(pet.weight || "-") + " kg</div><div><b>Trạng thái:</b> " + escapeThuCungHtml(pet.trangthai || "-") + "</div>" +
            "<div><b>Chủ nuôi:</b> " + escapeThuCungHtml(pet.owner?.name || "-") + "</div><div><b>Số điện thoại:</b> " + escapeThuCungHtml(pet.owner?.phone || "-") + "</div></div>" +
            "<h3>LỊCH SỬ KHÁM BỆNH</h3><table><thead><tr><th>STT</th><th>Mã phiếu</th><th>Ngày khám</th><th>Chẩn đoán</th><th>Trạng thái</th><th>Hóa đơn</th></tr></thead><tbody>" +
            (historyRows || "<tr><td colspan='5'>Chưa có lịch sử khám bệnh.</td></tr>") +
            "</tbody></table><script>window.onload=function(){window.print();};<\\/script></body></html>");
        w.document.close();
    } catch (error) { alert("Không thể in hồ sơ thú cưng: " + error.message); }
}


// Lịch tiêm toàn phòng khám, đánh dấu quá hạn và sắp đến hạn.
async function loadVaccinationSchedule() {
    const tbody = document.getElementById("vaccinationScheduleTable");
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Đang tải...</td></tr>';
    try {
        const response = await apiRequest("/vaccinations/schedule");
        const rows = extractList(response);
        tbody.innerHTML = rows.length ? rows.map(item => {
            const status = item.status === "QUA_HAN"
                ? '<span class="badge badge-danger">Quá hạn</span>'
                : item.status === "SAP_DEN_HAN"
                    ? '<span class="badge badge-warning">Sắp đến hạn</span>'
                    : '<span class="badge badge-success">Còn hạn</span>';
            return '<tr><td>' + escapeThuCungHtml(item.mathucung) + '</td><td>' + escapeThuCungHtml(item.ten_thucung) + '</td><td>' + escapeThuCungHtml(item.owner_name || "-") + '<br><small>' + escapeThuCungHtml(item.owner_phone || "") + '</small></td><td>' + escapeThuCungHtml(item.vaccine_name) + '</td><td>' + escapeThuCungHtml(item.vaccinated_at) + '</td><td>' + escapeThuCungHtml(item.next_due_date) + '</td><td>' + status + '</td></tr>';
        }).join("") : '<tr><td colspan="7" class="text-center text-muted">Chưa có lịch hẹn tiêm chủng.</td></tr>';
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-danger text-center">' + escapeThuCungHtml(error.message) + '</td></tr>';
    }
}
