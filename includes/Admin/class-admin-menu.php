<?php
namespace AFB\Admin;

class Class_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
    }

    public function add_menu_pages() {
        add_menu_page(
            __( 'AFB Forms', 'advanced-forms-builder' ),
            __( 'AFB Forms', 'advanced-forms-builder' ),
            'manage_options',
            'afb-forms',
            [ $this, 'render_admin_page' ],
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            'afb-forms', 
            __( 'Заявки', 'advanced-forms-builder' ),
            __( 'Заявки', 'advanced-forms-builder' ),
            'manage_options',
            'afb-entries', 
            [ $this, 'render_entries_page' ] 
        );
    }

    /**
     * Рендеринг главной страницы плагина (Наш Конструктор)
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Advanced Forms Builder — Конструктор', 'advanced-forms-builder' ); ?></h1>
            <p>Тестирование бэкенд-слоя сохранения структуры формы.</p>
            
            <hr>

            <div style="background: #fff; padding: 20px; max-width: 500px; border: 1px solid #ccd0d4; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <form id="afb-admin-builder-form" onsubmit="event.preventDefault(); return false;">
                    
                    <input type="hidden" id="afb_admin_rest_nonce" value="<?php echo wp_create_nonce( 'wp_rest' ); ?>">

                    <div style="margin-bottom: 15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Название новой формы:</label>
                        <input type="text" id="afb-new-form-title" placeholder="Например: Форма в подвале" style="width:100%; padding: 8px;" required>
                    </div>

                    <div style="margin-bottom: 20px; background: #f0f0f1; padding: 15px; border-left: 4px solid #0073aa;">
                        <strong style="display:block; margin-bottom: 5px;">Сгенерированная структура полей (JSON):</strong>
                        <small style="color: #646970;">При клике на «Сохранить» мы отправим на бэкенд массив из двух инпутов: Компанию и Телефон.</small>
                    </div>

                    <button type="submit" class="button button-primary button-large" id="afb-save-form-btn">
                        Создать форму в БД
                    </button>
                </form>

                <div id="afb-builder-response" style="margin-top: 15px; padding: 10px; display: none; font-weight: bold;"></div>
            </div>
        </div>
        <?php
        // Подключаем JS
        $this->render_inline_js();
    }
	
    /**
     * Прямой инжект скрипта в подвал WP (Обход всех багов очередей)
     */
    public function render_inline_js() {
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log('AFB ВНИМАНИЕ: Скрипт конструктора успешно инициализирован в футере!');

            const builderForm = document.getElementById('afb-admin-builder-form');
            const responseDiv = document.getElementById('afb-builder-response');

            if (!builderForm) {
                console.log('Форма конструктора не найдена на этой странице');
                return;
            }

            builderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Кнопка нажата, собираем JSON...');

                const formTitle = document.getElementById('afb-new-form-title').value;

                const mockupFields = [
                    { type: "text", name: "client_company", label: "Название компании", required: true, placeholder: "ООО Ромашка" },
                    { type: "text", name: "client_phone", label: "Номер телефона", required: true, placeholder: "+7 (999) 000-00-00" },
                    { type: "textarea", name: "client_comment", label: "Комментарий к заказу", required: false, placeholder: "Текст из конструктора" }
                ];

                responseDiv.style.display = 'block';
                responseDiv.style.backgroundColor = '#f0f0f1';
                responseDiv.style.color = '#1d2327';
                responseDiv.innerText = 'Сохранение формы...';

                // Пытаемся вытащить nonce из стандартных скрытых полей WP на этой странице
                let wpNonce = '';
                if (typeof wpApiSettings !== 'undefined') {
                    wpNonce = wpApiSettings.nonce;
                } else {
                    const wpInlineNonce = document.getElementById('_wpnonce') || document.getElementById('wp-comment-nonce');
                    wpNonce = wpInlineNonce ? wpInlineNonce.value : '';
                }

                fetch('/wp-json/afb/v1/forms/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wpNonce
                    },
                    body: JSON.stringify({
                        id: 0,
                        title: formTitle,
                        form_fields: mockupFields
                    })
                })
                .then(res => {
                    console.log('Статус ответа сервера:', res.status);
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
        </script>
        <?php
    }

    /**
     * Рендеринг страницы со списком заявок через WP_List_Table
     */
    public function render_entries_page() {
        require_once AFB_PATH . 'includes/admin/class-entries-list-table.php';

        $entries_table = new Class_Entries_List_Table();
        $entries_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Заявки из форм (Entries)', 'advanced-forms-builder' ); ?></h1>
            <hr class="wp-header-end">

            <form method="get">
                <input type="hidden" name="page" value="afb-entries" />
                <?php $entries_table->display(); ?>
            </form>
        </div>
        <?php
    }
}