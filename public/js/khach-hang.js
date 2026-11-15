const pubApi = '/api/v1/public';
let ownerFound = null;
let spaCatalog = [];
let lookupBookings = [];
let lookupPage = 1;
const $id = id => document.getElementById(id);
const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

async function pubRequest(path, options = {}) {
    const response = await fetch(pubApi + path, {
        ...options,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(options.headers || {}) }
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        if (data.errors && typeof window.showFormValidationErrors === "function") {
            window.showFormValidationErrors(data.errors);
        }
        throw new Error(data.message || Object.values(data.errors || {}).flat().join(' ') || 'Có lỗi xảy ra');
    }
    return data;
}

function showMsg(id, message, success = false) {
    const el = $id(id);
    el.className = 'alert ' + (success ? 'alert-success' : 'alert-danger');
    el.textContent = message;
    el.classList.remove('d-none');
}

function selectedServiceType() {
    const spa = $id('serviceSpa').checked;
    const medical = $id('serviceMedical').checked;
    if (spa && medical) return 'BOTH';
    if (spa) return 'SPA';
    if (medical) return 'MEDICAL';
    return null;
}

function dateAfterTomorrow() {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
}

function renderServices(target = 'spaServices', selected = []) {
    const el = $id(target);
    if (!spaCatalog.length) {
        el.innerHTML = '<p class="text-muted">Hiện chưa có dịch vụ Spa trong danh mục.</p>';
        return;
    }
    el.innerHTML = spaCatalog.map(service =>
        '<label class="spa-option"><input type="checkbox" value="' + Number(service.item_id) + '" ' +
        (selected.includes(Number(service.item_id)) ? 'checked' : '') + '> <strong>' +
        esc(service.ten_sanpham) + '</strong><br><small>' +
        Number(service.dongia).toLocaleString('vi-VN') + ' đ</small></label>'
    ).join('');
}

function toggleServices() {
    const spa = $id('serviceSpa').checked;
    const medical = $id('serviceMedical').checked;
    $id('medicalFields').classList.toggle('d-none', !medical);
    $id('spaFields').classList.toggle('d-none', !spa);
}

function selectedSpa(target = 'spaServices') {
    return [...$id(target).querySelectorAll('input:checked')].map(input => Number(input.value));
}

async function loadOwner() {
    const phone = $id('phone').value.trim();
    if (!phone) return alert('Nhập số điện thoại trước.');
    try {
        const response = await pubRequest('/owners?phone=' + encodeURIComponent(phone));
        ownerFound = response.found ? response.data : null;
        $id('petSelect').innerHTML = '<option value="">— Đăng ký thú cưng mới —</option>';
        if (ownerFound) {
            $id('ownerName').value = ownerFound.owner.ho_ten;
            $id('ownerEmail').value = ownerFound.owner.email || '';
            $id('ownerAddress').value = ownerFound.owner.diachi || '';
            ownerFound.pets.forEach(pet => $id('petSelect').insertAdjacentHTML(
                'beforeend',
                '<option value="' + Number(pet.pet_id) + '">' + esc(pet.ten_thucung) +
                ' · ' + esc(pet.loai) + ' · ' + esc(pet.mathucung) + '</option>'
            ));
            $id('ownerHint').textContent = 'Đã tìm thấy chủ nuôi. Chọn thú cưng cũ hoặc đăng ký thêm bé mới.';
        } else {
            $id('ownerName').value = '';
            $id('ownerEmail').value = '';
            $id('ownerAddress').value = '';
            $id('ownerHint').textContent = 'Chưa có hồ sơ với số điện thoại này. Điền thông tin chủ nuôi và thú cưng mới.';
        }
        togglePet();
    } catch (error) {
        showMsg('bookingMessage', error.message);
    }
}

function togglePet() {
    const existing = Boolean($id('petSelect').value);
    $id('newPetFields').classList.toggle('d-none', existing);
    $id('petName').required = !existing;
    $id('species').required = !existing;
}

function setShiftTime(shift, timeInput) {
    const morning = shift.value === 'SANG';
    timeInput.min = morning ? '07:00' : '14:00';
    timeInput.max = morning ? '11:00' : '17:00';
    if (timeInput.value < timeInput.min || timeInput.value > timeInput.max) {
        timeInput.value = morning ? '08:00' : '14:00';
    }
}

async function submitBooking(event) {
    event.preventDefault();
    const serviceType = selectedServiceType();
    if (!serviceType) return showMsg('bookingMessage', 'Vui lòng tích chọn ít nhất một dịch vụ.');
    const isNew = !$id('petSelect').value;
    const includesSpa = ['SPA', 'BOTH'].includes(serviceType);
    const payload = {
        phone: $id('phone').value.trim(),
        owner_id: ownerFound?.owner?.owner_id || null,
        owner_name: $id('ownerName').value.trim(),
        address: $id('ownerAddress').value,
        email: $id('ownerEmail').value,
        pet_id: $id('petSelect').value || null,
        new_pet: isNew ? 1 : 0,
        pet_name: $id('petName').value,
        species: $id('species').value,
        breed: $id('breed').value,
        gender: $id('gender').value,
        dob: $id('dob').value || null,
        service_type: serviceType,
        appointment_date: $id('appointmentDate').value,
        shift: $id('shift').value,
        preferred_time: $id('preferredTime').value,
        medical_note: $id('medicalNote').value,
        spa_services: includesSpa ? selectedSpa() : []
    };
    if (includesSpa && !payload.spa_services.length) {
        return showMsg('bookingMessage', 'Vui lòng chọn ít nhất một dịch vụ Spa.');
    }
    try {
        $id('submitBooking').disabled = true;
        const response = await pubRequest('/bookings', { method: 'POST', body: JSON.stringify(payload) });
        $id('bookingForm').classList.add('d-none');
        $id('bookingSuccess').classList.remove('d-none');
        $id('bookingSuccess').innerHTML =
            '<strong>Đặt lịch thành công!</strong><p class="mb-1">' + esc(response.message) +
            '</p><p>Mã lịch hẹn: <strong>' + esc(response.data.ma_lich_hen) +
            '</strong></p><p>Thú cưng: ' + esc(response.data.pet_name) +
            '</p><p class="mb-0">Bạn có thể tra cứu lịch bằng số điện thoại đã đăng ký.</p>';
        $id('lookupPhone').value = payload.phone;
    } catch (error) {
        showMsg('bookingMessage', error.message);
    } finally {
        $id('submitBooking').disabled = false;
    }
}

function renderLookupResults() {
    const el = $id('lookupResults');
    const pageSize = 5;
    const totalPages = Math.ceil(lookupBookings.length / pageSize);
    lookupPage = Math.min(Math.max(lookupPage, 1), Math.max(totalPages, 1));
    const start = (lookupPage - 1) * pageSize;
    const visibleBookings = lookupBookings.slice(start, start + pageSize);

    const rows = visibleBookings.map(booking =>
        '<div class="lookup-result-item"><div><strong>' + esc(booking.ma_lich_hen) +
        '</strong> · ' + esc(booking.ten_thucung) + '<br><small class="text-muted">' +
        esc(booking.ngay_hen) + ' · ' + (booking.ca_hen === 'SANG' ? 'Ca sáng' : 'Ca chiều') +
        ' · ' + esc(({MEDICAL:'Khám bệnh', SPA:'Dịch vụ Spa', BOTH:'Dịch vụ Spa và Khám bệnh'})[booking.loai_dich_vu] || booking.loai_dich_vu) +
        ' · ' + esc(booking.trang_thai) + '</small></div><button type="button" class="btn btn-sm btn-outline-primary" data-select-booking="' +
        Number(booking.id) + '">Xem thông tin</button></div>'
    ).join('');

    let pagination = '';
    if (totalPages > 1) {
        const pages = totalPages <= 7
            ? Array.from({length: totalPages}, (_, index) => index + 1)
            : [...new Set([1, lookupPage - 1, lookupPage, lookupPage + 1, totalPages])]
                .filter(page => page >= 1 && page <= totalPages).sort((a, b) => a - b);
        let pageButtons = '';
        pages.forEach((page, index) => {
            if (index && page - pages[index - 1] > 1) pageButtons += '<span class="mx-1 text-muted">...</span>';
            pageButtons += '<button type="button" class="btn btn-sm ' +
                (page === lookupPage ? 'btn-primary' : 'btn-outline-primary') +
                ' mx-1" data-lookup-page="' + page + '">' + page + '</button>';
        });
        pagination = '<div class="d-flex flex-wrap align-items-center justify-content-end mt-3">' +
            '<small class="text-muted mr-2">Trang ' + lookupPage + '/' + totalPages +
            ' · ' + lookupBookings.length + ' lịch hẹn</small>' +
            '<button type="button" class="btn btn-sm btn-outline-primary mx-1" data-lookup-page="' +
            (lookupPage - 1) + '"' + (lookupPage === 1 ? ' disabled' : '') + '>‹</button>' +
            pageButtons +
            '<button type="button" class="btn btn-sm btn-outline-primary mx-1" data-lookup-page="' +
            (lookupPage + 1) + '"' + (lookupPage === totalPages ? ' disabled' : '') + '>›</button></div>';
    }
    el.innerHTML = rows + pagination;
}

function selectBooking(id) {
    const booking = lookupBookings.find(item => Number(item.id) === Number(id));
    if (!booking) return;
    $id('bookingDetails').classList.remove('d-none');
    $id('lookupPetName').textContent = 'Lịch ' + booking.ma_lich_hen + ' · ' + booking.ten_thucung;
    $id('bookingOwnerInfo').innerHTML =
        'Họ tên: ' + esc(booking.owner_name) + '<br>Điện thoại: ' + esc(booking.phone) +
        '<br>Email: ' + esc(booking.email || '—') + '<br>Địa chỉ: ' + esc(booking.diachi || '—');
    $id('bookingPetInfo').innerHTML =
        'Tên: ' + esc(booking.ten_thucung) + '<br>Loài: ' + esc(booking.loai) +
        '<br>Giống: ' + esc(booking.giong || '—') + '<br>Giới tính: ' + esc(booking.gioitinh || '—');
    const serviceName = ({MEDICAL:'Khám bệnh', SPA:'Dịch vụ Spa', BOTH:'Dịch vụ Spa và Khám bệnh'})[booking.loai_dich_vu] || booking.loai_dich_vu;
    const spaNames = booking.spa_service_names?.length ? ' · Các dịch vụ Spa: ' + booking.spa_service_names.join(', ') : '';
    $id('bookingServiceInfo').textContent = 'Dịch vụ: ' + serviceName + spaNames;
    $id('bookingDateInfo').textContent = 'Ngày hẹn: ' + booking.ngay_hen +
        ' · ' + (booking.ca_hen === 'SANG' ? 'Ca sáng (07:00–11:00)' : 'Ca chiều (14:00–17:00)') +
        ' · Giờ mong muốn: ' + String(booking.gio_mong_muon || '').slice(0, 5);
    $id('bookingStatus').textContent = 'Trạng thái: ' + booking.trang_thai;
    $id('bookingNoteInfo').textContent = booking.ghi_chu_kham ? 'Ghi chú khám: ' + booking.ghi_chu_kham : 'Ghi chú khám: Không có';
    $id('bookingDetails').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function lookup(event) {
    event.preventDefault();
    $id('bookingDetails').classList.add('d-none');
    $id('lookupResults').innerHTML = '';
    try {
        const phone = $id('lookupPhone').value.trim();
        const response = await pubRequest('/bookings/lookup?phone=' + encodeURIComponent(phone));
        lookupBookings = response.data || [];
        lookupPage = 1;
        renderLookupResults();
        showMsg('lookupMessage', 'Tìm thấy ' + lookupBookings.length + ' lịch hẹn. Chọn một lịch để xem thông tin.', true);
    } catch (error) {
        lookupBookings = [];
        showMsg('lookupMessage', error.message);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    $id('appointmentDate').min = dateAfterTomorrow();
    $id('appointmentDate').value = dateAfterTomorrow();
    $id('findOwner').addEventListener('click', loadOwner);
    $id('phone').addEventListener('input', () => {
        ownerFound = null;
        $id('ownerHint').textContent = 'Nếu là khách cũ, hãy bấm Tìm hồ sơ sau khi nhập số điện thoại.';
    });
    $id('petSelect').addEventListener('change', togglePet);
    $id('serviceSpa').addEventListener('change', toggleServices);
    $id('serviceMedical').addEventListener('change', toggleServices);
    $id('shift').addEventListener('change', () => setShiftTime($id('shift'), $id('preferredTime')));
    $id('bookingForm').addEventListener('submit', submitBooking);
    $id('lookupForm').addEventListener('submit', lookup);
    $id('lookupResults').addEventListener('click', event => {
        const pageButton = event.target.closest('[data-lookup-page]');
        if (pageButton && !pageButton.disabled) {
            lookupPage = Number(pageButton.dataset.lookupPage);
            renderLookupResults();
            return;
        }
        const button = event.target.closest('[data-select-booking]');
        if (button) selectBooking(button.dataset.selectBooking);
    });
    toggleServices();
    togglePet();
    try {
        const response = await pubRequest('/spa-services');
        spaCatalog = response.data || [];
        renderServices();
    } catch (error) {
        $id('spaServices').textContent = 'Không tải được danh mục Spa: ' + error.message;
    }
});
