document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.afb-form-wrapper form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Отменяем стандартную перезагрузку страницы

            const formId = form.getAttribute('data-form-id');
            const submitBtn = form.querySelector('button[type="submit"]');
            const messageBox = form.querySelector('.afb-message-box');
            
            // Собираем данные полей формы
            const formData = new FormData(form);
            const fields = {};
            
            formData.forEach((value, key) => {
                // Если поле с таким именем уже есть (например, чекбоксы), делаем массив
                if (fields[key]) {
                    if (!Array.isArray(fields[key])) {
                        fields[key] = [fields[key]];
                    }
                    fields[key].push(value);
                } else {
                    fields[key] = value;
                }
            });

            // Блокируем кнопку на время отправки
            if (submitBtn) submitBtn.disabled = true;
            if (messageBox) {
                messageBox.className = 'afb-message-box info';
                messageBox.textContent = 'Отправка...';
            }

            // Настройки для REST API запроса
            // afb_vars мы прокинем из PHP через wp_localize_script
            fetch(afb_vars.rest_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': afb_vars.nonce // Передаем Nonce для проверки безопасности
                },
                body: JSON.stringify({
                    form_id: parseInt(formId),
                    fields: fields
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                if (res.status === 200 && res.body.success) {
                    messageBox.className = 'afb-message-box success';
                    messageBox.textContent = res.body.message;
                    form.reset(); // Очищаем форму при успехе
                } else {
                    messageBox.className = 'afb-message-box error';
                    messageBox.textContent = res.body.error || 'Произошла ошибка при отправке.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageBox.className = 'afb-message-box error';
                messageBox.textContent = 'Ошибка сети или сервера.';
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    });
});