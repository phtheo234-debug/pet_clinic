(function () {
    const excludedTypes = new Set(["hidden", "checkbox", "radio", "button", "submit", "reset", "image", "file"]);
    const selectors = "input, textarea";
    let feedbackSequence = 0;

    function fieldContainer(field) {
        return field.closest(".form-group, .form-field, .mb-3, .col, [class*='col-']") || field.parentElement;
    }

    function guidanceText(field) {
        const custom = field.getAttribute("data-guidance");
        if (custom) return custom;
        const parts = [];
        const label = field.id
            ? document.querySelector('label[for="' + CSS.escape(field.id) + '"]')?.textContent || ""
            : field.closest(".form-group")?.querySelector("label")?.textContent || "";
        const context = (label + " " + (field.placeholder || "") + " " + field.id).toLowerCase();
        parts.push(field.required ? "Bắt buộc nhập." : "Không bắt buộc; chỉ nhập nếu có thông tin.");
        const minLength = field.getAttribute("minlength");
        const maxLength = field.getAttribute("maxlength");
        if (minLength && maxLength) parts.push("Độ dài từ " + minLength + " đến " + maxLength + " ký tự.");
        else if (minLength) parts.push("Tối thiểu " + minLength + " ký tự.");
        else if (maxLength) parts.push("Tối đa " + maxLength + " ký tự.");
        if (field.pattern) parts.push("Định dạng phải khớp mẫu quy định trong ô nhập.");
        if (field.type === "email") parts.push("Nhập địa chỉ email hợp lệ (ví dụ: ten@domain.com).");
        if (field.type === "tel" || /điện thoại|số điện thoại|phone/.test(context)) parts.push("Chỉ nhập số điện thoại hợp lệ; không nhập chữ. Nếu có giới hạn độ dài hoặc mẫu, phải tuân thủ đúng giới hạn hiển thị.");
        if (/họ tên|tên chủ|tên thú|tên sản phẩm|tên lồng|tên phẫu thuật/.test(context)) parts.push("Nhập đầy đủ tên, không để trống nếu trường được đánh dấu bắt buộc.");
        if (/mã |mã$|code/.test(context)) parts.push("Nhập mã đúng tiền tố và cấu trúc được hướng dẫn; không tự ý thêm khoảng trắng.");
        if (/tìm kiếm|tra cứu|search/.test(context)) parts.push("Nhập từ khóa cần tìm; có thể tìm theo tên hoặc mã nếu trang hỗ trợ.");
        if (field.type === "number") {
            if (field.min !== "") parts.push("Giá trị nhỏ nhất: " + field.min + ".");
            if (field.max !== "") parts.push("Giá trị lớn nhất: " + field.max + ".");
            if (field.step && field.step !== "any") parts.push(field.step === "1" ? "Chỉ nhập số nguyên." : "Bước tăng/giảm: " + field.step + ".");
            if (field.min === "" && /giá|đơn giá|chi phí/.test(context)) parts.push("Chỉ nhập số tiền từ 0 trở lên, không nhập ký hiệu tiền tệ.");
            if (field.min === "" && /số lượng|cân nặng|trọng lượng/.test(context)) parts.push("Chỉ nhập số hợp lệ lớn hơn 0.");
        }
        if (field.type === "date" || field.type === "time") {
            if (field.min) parts.push("Không được trước " + field.min + ".");
            if (field.max) parts.push("Không được sau " + field.max + ".");
        }
        if (field.type === "password" && !minLength && !maxLength) parts.push("Đáp ứng quy tắc mật khẩu được thông báo trên biểu mẫu.");
        if (field.tagName === "TEXTAREA") parts.push("Không vượt quá giới hạn ký tự được quy định.");
        if (parts.length === 1 && !field.required && !maxLength && !minLength && !field.pattern && field.type !== "email" && field.type !== "tel") {
            parts.push("Nhập đúng nội dung theo nhãn và hướng dẫn của biểu mẫu.");
        }
        return parts.join(" ");
    }

    function getFeedback(field) {
        if (field.dataset.guidanceFeedbackId) {
            return document.getElementById(field.dataset.guidanceFeedbackId);
        }
        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback d-block";
        feedback.id = "field-feedback-" + (++feedbackSequence);
        feedback.hidden = true;
        field.dataset.guidanceFeedbackId = feedback.id;
        const group = field.closest(".input-group");
        (group || field).insertAdjacentElement("afterend", feedback);
        return feedback;
    }

    function setError(field, message) {
        if (!field || field.disabled || field.readOnly) return;
        field.classList.add("is-invalid");
        const feedback = getFeedback(field);
        if (feedback) {
            feedback.textContent = message;
            feedback.hidden = false;
        }
    }

    window.setFormFieldError = setError;

    function clearError(field) {
        field.classList.remove("is-invalid");
        const feedback = getFeedback(field);
        if (feedback) {
            feedback.textContent = "";
            feedback.hidden = true;
        }
    }

    function decorate(root) {
        (root || document).querySelectorAll(selectors).forEach(field => {
            if (field.dataset.guidanceReady === "1" || excludedTypes.has((field.type || "").toLowerCase()) || field.readOnly || field.disabled) return;
            field.dataset.guidanceReady = "1";
            const group = field.closest(".input-group");
            const anchor = group || field;
            const next = anchor.nextElementSibling;
            const hasUsefulHint = next && next.matches(".form-text, .help-block, [data-field-guidance]") &&
                !next.classList.contains("d-none") && Boolean(next.textContent.trim());
            if (!hasUsefulHint) {
                const note = document.createElement("small");
                note.className = "form-text text-muted";
                note.dataset.fieldGuidance = "1";
                note.textContent = guidanceText(field);
                anchor.insertAdjacentElement("afterend", note);
            }
            field.addEventListener("input", () => {
                if (field.validity.valid) clearError(field);
            });
            field.addEventListener("change", () => {
                if (field.validity.valid) clearError(field);
            });
        });
    }

    document.addEventListener("invalid", event => {
        const field = event.target;
        if (!field || !field.matches || !field.matches(selectors)) return;
        event.preventDefault();
        const validity = field.validity;
        let message = field.validationMessage || "Thông tin chưa hợp lệ. Vui lòng kiểm tra lại.";
        if (validity.valueMissing) message = "Trường này bắt buộc phải nhập.";
        else if (validity.typeMismatch && field.type === "email") message = "Email không đúng định dạng. Ví dụ: ten@domain.com.";
        else if (validity.tooShort) message = "Nội dung quá ngắn; vui lòng nhập ít nhất " + field.minLength + " ký tự.";
        else if (validity.tooLong) message = "Nội dung vượt quá " + field.maxLength + " ký tự.";
        else if (validity.rangeUnderflow) message = "Giá trị không được nhỏ hơn " + field.min + ".";
        else if (validity.rangeOverflow) message = "Giá trị không được lớn hơn " + field.max + ".";
        else if (validity.stepMismatch) message = "Giá trị không đúng bước nhập cho phép.";
        else if (validity.patternMismatch) message = "Dữ liệu không đúng định dạng yêu cầu.";
        setError(field, message);
    }, true);

    window.showFormValidationErrors = function (errors) {
        if (!errors || typeof errors !== "object") return;
        const forms = Array.from(document.querySelectorAll("form")).filter(form => form.offsetParent !== null);
        const activeForm = document.activeElement?.closest("form");
        const form = activeForm || forms[0];
        Object.entries(errors).forEach(([key, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : String(messages);
            const simpleKey = key.split(".").pop();
            const normalize = value => String(value || "").toLowerCase().replace(/[^a-z0-9]/g, "");
            const normalizedKey = normalize(simpleKey);
            const aliases = {
                ho_ten: ["hoten", "hovaten", "newname", "editname", "profilename", "ownername"],
                owner_name: ["ownername", "hotenchunuoi", "ownername"],
                pet_name: ["petname", "tenthuccung"],
                name: ["name", "ten", "tenthuccung", "inventoryname", "spaservicename", "surgeryname"],
                appointment_date: ["appointmentdate"],
                preferred_time: ["preferredtime"],
                phone: ["phone", "sodienthoai", "lookupphone"],
                email: ["email", "owneremail", "profileemail"]
            };
            const accepted = [normalizedKey, ...(aliases[simpleKey] || [])];
            const candidates = form ? Array.from(form.querySelectorAll("input, textarea")) : [];
            const field = candidates.find(item => {
                const id = normalize(item.id);
                const name = normalize(item.name);
                const label = normalize(item.closest(".form-group")?.querySelector("label")?.textContent);
                return item.name === key || item.name === simpleKey || item.id === key || item.id === simpleKey ||
                    accepted.some(alias => alias && (id === alias || name === alias || id.includes(alias) || label.includes(alias)));
            });
            if (field) setError(field, message);
        });
    };

    function init() { decorate(document); }
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init, { once: true });
    else init();

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
            if (node.nodeType === 1) {
                if (node.matches?.(selectors)) decorate(node.parentElement);
                else decorate(node);
            }
        }));
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });

    const attributeObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            const field = mutation.target;
            const note = (field.closest(".input-group") || field).nextElementSibling;
            if (note?.matches("[data-field-guidance]")) note.textContent = guidanceText(field);
        });
    });
    attributeObserver.observe(document.documentElement, {
        attributes: true,
        subtree: true,
        attributeFilter: ["min", "max", "required", "pattern", "minlength", "maxlength", "step"]
    });
})();