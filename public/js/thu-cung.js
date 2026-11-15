let petsList = [];
let currentUserRole = "";

function generateNextPetCode() {
    let maxNum = 0;
    petsList.forEach(p => {
        const match = String(p.mathucung || "").match(/\d+/);
        if (match) maxNum = Math.max(maxNum, parseInt(match[0], 10));
    });
    return "PET-" + String(maxNum + 1).padStart(3, "0");
}

function normalizeGender(raw) {
    const value = String(raw || "").trim().toUpperCase();
    if (["DUC", "ĐỰC", "MALE"].includes(value)) return "DUC";
    if (["CAI", "CÁI", "FEMALE"].includes(value)) return "CAI";
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
    const currentUser = getCurrentUser();
    currentUserRole = String(currentUser?.vaitro || "").trim().toUpperCase();

    await loadPets();

    const canReceivePets = ["QUAN_TRI", "NHAN_VIEN"].includes(currentUserRole);
    const addButton = document.getElementById("btnOpenAddPet");
    if (addButton && canReceivePets) addButton.classList.remove("d-none");

    const medicalCheckbox = document.getElementById("kham_benh");
    const medicalNoteGroup = document.getElementById("medicalNoteGroup");
    medicalCheckbox?.addEventListener("change", () => {
        medicalNoteGroup?.classList.toggle("d-none", !medicalCheckbox.checked);
        if (!medicalCheckbox.checked) {
            const note = document.getElementById("ghi_chu_kham");
            if (note) note.value = "";
        }
    });

    $("#addPetModal").on("show.bs.modal", () => {
        const code = document.getElementById("mathucung");
        if (code) code.value = generateNextPetCode();
    });

    const searchInput = document.getElementById("searchPet");
    const suggestionList = document.getElementById("ownerPhoneSuggestions");

    function refreshPhoneSuggestions() {
        if (!suggestionList) return;
        const unique = new Map();
        petsList.forEach(p => {
            const phone = String(p.owner?.phone || "");
            if (phone && !unique.has(phone)) unique.set(phone, p.owner?.name || "Khách hàng");
        });
        suggestionList.innerHTML = Array.from(unique.entries())
            .map(([phone, name]) => `<option value="${escapePetHtml(phone)}" label="${escapePetHtml(name)}"></option>`)
            .join("");
    }

    function runPetSearch() {
        const phone = String(searchInput?.value || "").replace(/\D/g, "");
        const filtered = phone
            ? petsList.filter(p => String(p.owner?.phone || "").replace(/\D/g, "").includes(phone))
            : petsList;
        renderPetsTable(filtered);
    }

    refreshPhoneSuggestions();
    document.getElementById("btnSearchPet")?.addEventListener("click", runPetSearch);
    searchInput?.addEventListener("input", runPetSearch);
    searchInput?.addEventListener("keydown", event => {
        if (event.key === "Enter") runPetSearch();
    });

    document.getElementById("petForm")?.addEventListener("submit", async event => {
        event.preventDefault();
        if (!canReceivePets) {
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
        const gender = normalizeGender(document.getElementById("gioitinh").value);
        const weight = Number(document.getElementById("can_nang").value);
        const khamBenh = Boolean(medicalCheckbox?.checked);
        const ghiChuKham = khamBenh ? document.getElementById("ghi_chu_kham").value.trim() : null;

        if (!ownerName || !ownerPhone) {
            alert("Vui lòng nhập họ tên và số điện thoại chủ nuôi.");
            return;
        }
        if (!petName) {
            alert("Vui lòng nhập tên thú cưng.");
            return;
        }
        if (!Number.isFinite(weight) || weight <= 0) {
            alert("Cân nặng thú cưng phải lớn hơn 0 kg.");
            document.getElementById("can_nang").focus();
            return;
        }

        const button = document.getElementById("btnSavePet");
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang lưu...';

        try {
            let ownerId = null;
            const owners = extractList(await apiRequest("/owners"));
            const existingOwner = owners.find(o => String(o.phone || "").trim() === ownerPhone);
            if (existingOwner) ownerId = existingOwner.owner_id ?? existingOwner.id;

            const payload = {
                owner_id: ownerId,
                ho_ten: ownerName,
                phone: ownerPhone,
                email: ownerEmail || null,
                diachi: ownerAddress || null,
                name: petName,
                species,
                breed,
                gender,
                dob,
                weight,
                kham_benh: khamBenh,
                ghi_chu_kham: ghiChuKham
            };

            await apiRequest("/pets", {
                method: "POST",
                body: JSON.stringify(payload)
            });

            event.target.reset();
            medicalNoteGroup?.classList.add("d-none");
            $("#addPetModal").modal("hide");
            await loadPets();
            refreshPhoneSuggestions();
            alert(khamBenh
                ? "Đã tiếp nhận hồ sơ và tự động đưa thú cưng vào danh sách chờ khám."
                : "Đã tiếp nhận hồ sơ thú cưng. Hồ sơ được ghi nhận là Không khám.");
        } catch (error) {
            alert("Không thể tiếp nhận thú cưng: " + error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save mr-1"></i> Lưu Hồ sơ';
        }
    });
});

async function loadPets() {
    const body = document.getElementById("petsTableBody");
    try {
        const response = await apiRequest("/pets");
        petsList = extractList(response);
        renderPetsTable(petsList);
    } catch (error) {
        body.innerHTML = `<tr><td colspan="8" class="text-danger text-center">${escapePetHtml(error.message)}</td></tr>`;
    }
}

function renderPetsTable(items) {
    const body = document.getElementById("petsTableBody");
    const statusLabels = {
        KHONG_KHAM: "Không khám",
        CHO_KHAM: "Chờ khám",
        DANG_KHAM: "Đang khám",
        DA_KHAM: "Đã khám"
    };

    if (!items.length) {
        body.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không tìm thấy hồ sơ phù hợp.</td></tr>';
        return;
    }

    body.innerHTML = items.map((pet, index) => {
        const petId = Number(pet.id ?? pet.pet_id);
        const status = pet.trangthai_kham || "KHONG_KHAM";
        const statusClass = status === "DA_KHAM" ? "badge-success"
            : status === "DANG_KHAM" ? "badge-warning"
            : status === "CHO_KHAM" ? "badge-info" : "badge-secondary";
        const notes = [
            pet.ngay_hen_kham ? "Lịch hẹn: " + pet.ngay_hen_kham + (pet.gio_hen_kham ? " · " + String(pet.gio_hen_kham).slice(0, 5) : "") : "",
            pet.ghi_chu_dat_lich ? "Ghi chú đặt lịch: " + pet.ghi_chu_dat_lich : "",
            pet.ghi_chu_tiep_nhan ? "Ghi chú tiếp nhận: " + pet.ghi_chu_tiep_nhan : ""
        ].filter(Boolean).map(note => `<small class="d-block text-muted mt-1">${escapePetHtml(note)}</small>`).join("");
        const ownerName = pet.owner?.name || "-";
        const ownerPhone = pet.owner?.phone || "-";
        const weight = pet.weight ?? "-";
        const stayLabels = {
            NOI_TRU: "Nội trú",
            NGOAI_TRU: "Ngoại trú",
            DA_XUAT_VIEN: "Đã xuất viện",
            CHUYEN_VIEN: "Chuyển viện"
        };
        const stayClass = pet.trangthai === "NOI_TRU" ? "badge-warning"
            : pet.trangthai === "CHUYEN_VIEN" ? "badge-danger"
            : pet.trangthai === "DA_XUAT_VIEN" ? "badge-secondary" : "badge-success";

        return `
            <tr>
                <td>${index + 1}</td>
                <td class="font-weight-bold text-primary">${escapePetHtml(pet.mathucung || "-")}</td>
                <td><strong>${escapePetHtml(pet.name || "-")}</strong><br><small class="text-muted">${escapePetHtml(pet.species || "-")}${pet.breed ? " · " + escapePetHtml(pet.breed) : ""}</small></td>
                <td>${escapePetHtml(ownerName)}<br><small class="text-muted">${escapePetHtml(ownerPhone)}</small></td>
                <td>${weight !== "-" && weight !== null ? escapePetHtml(weight) + " kg" : "-"}</td>
                <td><span class="badge ${stayClass}">${escapePetHtml(stayLabels[pet.trangthai] || pet.trangthai || "-")}</span></td>
                <td><span class="badge ${statusClass}">${escapePetHtml(statusLabels[status] || "Không khám")}</span>${notes}</td>
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="openPetDetail(${petId})"><i class="fas fa-eye"></i> Chi tiết</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="printPetProfile(${petId})"><i class="fas fa-print"></i> In hồ sơ</button>
                </td>
            </tr>
        `;
    }).join("");
}

function escapePetHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, char => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
    })[char]);
}

function formatPetDate(value) {
    if (!value) return "-";
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? escapePetHtml(value) : date.toLocaleString("vi-VN");
}

async function openPetDetail(petId) {
    const content = document.getElementById("petDetailContent");
    content.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Đang tải thông tin...</div>';
    $("#petDetailModal").modal("show");

    try {
        const response = await apiRequest(`/pets/${petId}`);
        const pet = response?.data?.data || response?.data || response;
        const history = Array.isArray(pet.medical_history) ? pet.medical_history : [];
        const examStatusLabels = {
            KHONG_KHAM: "Không khám",
            CHO_KHAM: "Chờ khám",
            DANG_KHAM: "Đang khám",
            DA_KHAM: "Đã khám",
        };
        const currentExamStatus = pet.trangthai_kham || "KHONG_KHAM";
        const historyRows = history.length ? history.map((item) => `
            <tr><td>${escapePetHtml(item.record_id)}</td><td>${formatPetDate(item.ngaykham)}</td><td>${escapePetHtml(item.chandoan || "-")}</td><td>${escapePetHtml(item.ten_phauthuat || item.huongxuly || "-")}</td><td>${escapePetHtml(item.huong_xuly || "-")}</td><td>${escapePetHtml(item.mahoadon || (item.invoice_id ? "Đã thanh toán" : "Chưa thanh toán"))}</td></tr>
        `).join("") : '<tr><td colspan="6" class="text-center text-muted">Chưa có lịch sử khám bệnh</td></tr>';

        const examStatusControl = `<span class="badge ${currentExamStatus === "DA_KHAM" ? "badge-success" : currentExamStatus === "DANG_KHAM" ? "badge-warning" : currentExamStatus === "CHO_KHAM" ? "badge-info" : "badge-secondary"}">${escapePetHtml(examStatusLabels[currentExamStatus] || "Không khám")}</span>`;

        content.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-6 mb-3"><strong>Mã thú cưng:</strong><br>${escapePetHtml(pet.mathucung || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Tên:</strong><br>${escapePetHtml(pet.name || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Loài / Giống:</strong><br>${escapePetHtml(pet.species || "-")} / ${escapePetHtml(pet.breed || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Ngày sinh:</strong><br>${escapePetHtml(pet.dob || "Chưa cập nhật")}</div>
                <div class="col-md-6 mb-3"><strong>Chủ nuôi:</strong><br>${escapePetHtml(pet.owner?.name || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Số điện thoại:</strong><br>${escapePetHtml(pet.owner?.phone || "-")}</div>
                <div class="col-md-6 mb-3"><strong>Trạng thái lưu trú:</strong><br>${escapePetHtml(pet.trangthai || "-")}</div>
            </div>

            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary"><i class="fas fa-weight mr-1"></i>Cân nặng hiện tại</h6>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-7 mb-2">
                            <label for="detailPetWeight">Cân nặng (kg)</label>
                            <input type="number" id="detailPetWeight" class="form-control" min="0.1" max="999" step="0.1" value="${pet.weight ?? ""}" placeholder="Nhập cân nặng">
                        </div>
                        <div class="form-group col-md-5 mb-2">
                            <button type="button" id="btnUpdatePetWeight" class="btn btn-primary btn-block">Lưu cân nặng</button>
                        </div>
                    </div>
                    <small class="text-muted">Cân nặng này là giá trị hiện tại và sẽ được cập nhật trực tiếp vào hồ sơ thú cưng.</small>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="font-weight-bold text-primary"><i class="fas fa-stethoscope mr-1"></i>Trạng thái khám</h6>
                <div class="mt-2">${examStatusControl}</div>
                <small class="form-text text-muted">Trạng thái được hệ thống tự động cập nhật theo hàng đợi khám; không thể chỉnh sửa tại hồ sơ.</small>
            </div>

            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-history mr-1"></i>Lịch sử khám bệnh</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light"><tr><th>Mã phiếu</th><th>Ngày khám</th><th>Chẩn đoán</th><th>Hướng xử lý</th><th>Trạng thái</th><th>Hóa đơn</th></tr></thead>
                    <tbody>${historyRows}</tbody>
                </table>
            </div>
        `;

        document.getElementById("btnUpdatePetWeight").addEventListener("click", async () => {
            const input = document.getElementById("detailPetWeight");
            const weight = Number(input.value);
            if (!Number.isFinite(weight) || weight <= 0 || weight > 999) {
                alert("Vui lòng nhập cân nặng lớn hơn 0 và không vượt quá 999 kg.");
                input.focus();
                return;
            }
            const button = document.getElementById("btnUpdatePetWeight");
            button.disabled = true;
            try {
                await apiRequest(`/pets/${petId}`, {
                    method: "PUT",
                    body: JSON.stringify({ weight }),
                });
                await loadPets();
                alert("Đã cập nhật cân nặng.");
                await openPetDetail(petId);
            } catch (err) {
                alert("Không cập nhật được cân nặng: " + err.message);
                button.disabled = false;
            }
        });


    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger mb-0">Không tải được chi tiết thú cưng: ${escapePetHtml(err.message)}</div>`;
    }
}


/** In hồ sơ thú cưng và lịch sử lưu trú. */
async function printPetProfile(petId) {
    try {
        const response = await apiRequest("/pets/" + petId);
        const pet = response?.data?.data || response?.data || {};
        const history = Array.isArray(pet.medical_history) ? pet.medical_history : [];
        const historyRows = history.map((item, index) =>
            "<tr><td>" + (index + 1) + "</td><td>#" + escapePetHtml(item.record_id) + "</td><td>" + formatPetDate(item.ngaykham) +
            "</td><td>" + escapePetHtml(item.chandoan || "-") + "</td><td>" + escapePetHtml(item.huong_xuly || "-") +
            "</td><td>" + escapePetHtml(item.mahoadon || (item.invoice_id ? "Đã thanh toán" : "Chưa thanh toán")) + "</td></tr>"
        ).join("");
        const w = window.open("", "_blank", "width=900,height=750");
        if (!w) { alert("Trình duyệt đang chặn cửa sổ in. Hãy cho phép popup rồi thử lại."); return; }
        w.document.write("<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Hồ sơ thú cưng</title><style>body{font-family:Arial;padding:28px;color:#222}h2,h3{text-align:center}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:24px 0}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border:1px solid #777;padding:8px;text-align:left}</style></head><body>" +
            "<h2>PET CLINIC</h2><h3>HỒ SƠ THÚ CƯNG</h3><div class='grid'>" +
            "<div><b>Mã thú cưng:</b> " + escapePetHtml(pet.mathucung || "-") + "</div><div><b>Tên:</b> " + escapePetHtml(pet.name || "-") + "</div>" +
            "<div><b>Loài:</b> " + escapePetHtml(pet.species || "-") + "</div><div><b>Giống:</b> " + escapePetHtml(pet.breed || "-") + "</div>" +
            "<div><b>Giới tính:</b> " + escapePetHtml(pet.gender || "-") + "</div><div><b>Ngày sinh:</b> " + escapePetHtml(pet.dob || "-") + "</div>" +
            "<div><b>Cân nặng hiện tại:</b> " + escapePetHtml(pet.weight || "-") + " kg</div><div><b>Trạng thái:</b> " + escapePetHtml(pet.trangthai || "-") + "</div>" +
            "<div><b>Chủ nuôi:</b> " + escapePetHtml(pet.owner?.name || "-") + "</div><div><b>Số điện thoại:</b> " + escapePetHtml(pet.owner?.phone || "-") + "</div></div>" +
            "<h3>LỊCH SỬ KHÁM BỆNH</h3><table><thead><tr><th>STT</th><th>Mã phiếu</th><th>Ngày khám</th><th>Chẩn đoán</th><th>Trạng thái</th><th>Hóa đơn</th></tr></thead><tbody>" +
            (historyRows || "<tr><td colspan='5'>Chưa có lịch sử khám bệnh.</td></tr>") +
            "</tbody></table><script>window.onload=function(){window.print();};<\\/script></body></html>");
        w.document.close();
    } catch (error) { alert("Không thể in hồ sơ thú cưng: " + error.message); }
}


