document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('submit', function (e) {
        if (!e.target.classList.contains('afb-generator-form')) {
            return;
        }

        e.preventDefault();

        const form = e.target;
        const responseDiv = form.querySelector('.afb-form-response');
        const submitBtn = form.querySelector('.afb-submit-btn');
        
        const formId = parseInt(form.querySelector('.afb-form-id-field').value, 10);

        // Красиво собираем поля формы в объект fields
        const fields = {};
        const formData = new FormData(form);
        
        formData.forEach((value, key) => {
            // Парсим поля вида fields[user_name] -> извлекаем "user_name"
            if (key.startsWith('fields[')) {
                const realKey = key.match(/fields\[(.*?)\]/)[1];
                fields[realKey] = value;
            }
        });

        // Формируем JSON-пакет для твоего REST API контроллера
        const requestData = {
            form_id: formId,
            fields: fields
        };

        if (submitBtn) submitBtn.disabled = true;
        if (responseDiv) {
            responseDiv.style.color = '#000';
            responseDiv.textContent = 'Отправка данных...';
        }

        // Стреляем напрямую в твой кастомный REST роут
        fetch('/wp-json/afb/v1/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            
            // WordPress REST API возвращает обычные свойства (или data объект при ошибках)
            if (data.success) {
                if (responseDiv) {
                    responseDiv.style.color = 'green';
                    responseDiv.textContent = data.message || 'Форма успешно отправлена!';
                }
                form.reset();
            } else {
                if (responseDiv) {
                    responseDiv.style.color = 'red';
                    responseDiv.textContent = data.message || data.error || 'Произошла ошибка.';
                }
            }
        })
        .catch(error => {
            if (submitBtn) submitBtn.disabled = false;
            if (responseDiv) {
                responseDiv.style.color = 'red';
                responseDiv.textContent = 'Ошибка сети при работе с REST API.';
            }
            console.error('AFB REST Error:', error);
        });
    });
});