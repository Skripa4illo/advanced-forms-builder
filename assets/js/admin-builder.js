document.addEventListener('DOMContentLoaded', function() {
    const builderForm = document.getElementById('afb-admin-builder-form');
    const responseDiv = document.getElementById('afb-builder-response');

    if (!builderForm) return;

    builderForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formTitle = document.getElementById('afb-new-form-title').value;

        // Имитируем набор полей, который в будущем будет собирать Vue/React интерфейс
        const mockupFields = [
            { type: "text", name: "client_company", label: "Название компании", required: true, placeholder: "ООО Ромашка" },
            { type: "text", name: "client_phone", label: "Номер телефона", required: true, placeholder: "+7 (999) 000-00-00" },
            { type: "textarea", name: "client_comment", label: "Комментарий к заказу", required: false, placeholder: "Ваш текст..." }
        ];

        responseDiv.style.display = 'block';
        responseDiv.style.backgroundColor = '#f0f0f1';
        responseDiv.style.color = '#1d2327';
        responseDiv.innerText = 'Сохранение формы...';

        fetch('/wp-json/afb/v1/forms/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : ''
            },
            body: JSON.stringify({
                id: 0, // 0 — создаем новую запись
                title: formTitle,
                form_fields: mockupFields
            })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Ошибка сервера: ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                responseDiv.style.backgroundColor = '#edfaef';
                responseDiv.style.color = '#00a32a';
                responseDiv.innerHTML = `🎉 Успех! ${data.message} <br>Создан новый Form ID: <strong>${data.id}</strong>`;
                builderForm.reset();
            } else {
                responseDiv.style.backgroundColor = '#fcf0f1';
                responseDiv.style.color = '#d63638';
                responseDiv.innerText = data.error || 'Произошла ошибка при сохранении.';
            }
        })
        .catch(err => {
            responseDiv.style.backgroundColor = '#fcf0f1';
            responseDiv.style.color = '#d63638';
            responseDiv.innerText = 'Сбой запроса: ' + err.message;
        });
    });
});