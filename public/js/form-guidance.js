(function () {
    const excludedTypes = new Set(["hidden", "checkbox", "radio", "button", "submit", "reset", "image", "file"]);
    const selectors = "input, textarea";

    function fieldContainer(field) {
        return field.closest(".form-group, .form-field, .mb-3, .col, [class*='col-']") || field.parentElement;
    }

    function guidanceText(field) {
        const custom = field.getAttribute("data-guidance");
        if (custom) return custom;
        const parts = [];
        parts.push(field.required ? "Bắt buộc nhập." : "Không bắt buộc; chỉ nhập nếu có thông tin.");
        const minLength = field.getAttribute("minlength");
        const maxLength = field.getAttribute("maxlength");
        if (minLength && maxLength) parts.push("Độ dài từ " + minLength + " đến " + maxLength + " ký tự.");
        else if (minLength) parts.push("Tối thiểu " + minLength + " ký tự.");
        else if (maxLength) parts.push("Tối đa " + maxLength + " ký tự.");
        if (field.pattern) parts.push("Định dạng phải khớp mẫu quy định trong ô nhập.");
        if (field.type === "email") parts.push("Nhập địa chỉ email hợp lệ (ví dụ: ten@domain.com).");
        if (field.type === "tel") parts.push("Chỉ nhập số điện thoại hợp lệ; không nhập chữ.");
        if (field.type === "number") {
            if (field.min !== "") parts.push("Giá trị nhỏ nhất: " + field.min + ".");
            if (field.max !== "") parts.push("Giá trị lớn nhất: " + field.max + ".");
            if (field.step && field.step !== "any") parts.push(field.step === "1" ? "Chỉ nhập số nguyên." : "Bước tăng/giảm: " + field.step + ".");
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
        const container = fieldContainer(field);
        if (!container) return null;
        let feedback = container.querySelector('[data-field-feedback="' + field.id + '"]');
        if (!feedback) {
            feedback = document.createElement("div");
            feedback.className = "invalid-feedback d-block";
            feedback.dataset.fieldFeedback = field.id || ("field-" + Math.random().toString(36).slice(2));
            feedback.hidden = true;
            const group = field.closest(".input-group");
            const anchor = group || field;
            anchor.insertAdjacentElement("afterend", feedback);
        }
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
            const container = fieldContainer(field);
            if (container && !container.querySelector(".form-text, .help-block, [data-field-guidance]")) {
                const note = document.createElement("small");
                note.className = "form-text text-muted";
                note.dataset.fieldGuidance = "1";
                note.textContent = guidanceText(field);
                const group = field.closest(".input-group");
                (group || field).insertAdjacentElement("afterend", note);
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
            const candidates = form ? Array.from(form.querySelectorAll("input, textarea")) : [];
            const field = candidates.find(item =>
                item.name === key || item.name === simpleKey || item.id === key || item.id === simpleKey ||
                item.id.toLowerCase().includes(simpleKey.toLowerCase())
            );
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
})();