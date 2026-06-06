<?php
namespace AFB\Admin;

class Class_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );

        // Регистрируем обработчик AJAX для авторизованных админов
        add_action( 'wp_ajax_afb_save_form_builder', [ $this, 'handle_ajax_save_form' ] );
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
     * Логика сохранения через проверенный временем admin-ajax.php
     */
    public function handle_ajax_save_form() {
        // Проверяем безопасность (Nonce) для AJAX
        check_ajax_referer( 'afb_ajax_builder_action', 'security' );

        // Проверяем права админа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'error' => 'У вас недостаточно прав' ], 403 );
        }

        global $wpdb;
        $table_forms = $wpdb->prefix . 'afb_forms';

        // Читаем сырой JSON из тела запроса (fetch)
        $json_input = file_get_contents( 'php://input' );
        $params     = json_decode( $json_input, true );

        $form_id = isset( $params['id'] ) ? absint( $params['id'] ) : 0;
        $title   = isset( $params['title'] ) ? sanitize_text_field( $params['title'] ) : '';
        $fields  = isset( $params['form_fields'] ) ? $params['form_fields'] : [];

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'error' => 'Название формы обязательно' ], 400 );
        }

        $json_fields = wp_json_encode( $fields );

        $data_to_save = [
            'title'       => $title,
            'form_fields' => $json_fields,
            'created_at'  => current_time( 'mysql' )
        ];

        if ( $form_id > 0 ) {
            $wpdb->update( $table_forms, $data_to_save, [ 'id' => $form_id ] );
            wp_send_json_success( [ 'message' => 'Форма успешно обновлена!', 'id' => $form_id ] );
        } {
            $inserted = $wpdb->insert( $table_forms, $data_to_save );
            if ( ! $inserted ) {
                wp_send_json_error( [ 'error' => 'Ошибка БД при вставке' ], 500 );
            }
            wp_send_json_success( [ 'message' => 'Новая форма успешно создана!', 'id' => $wpdb->insert_id ] );
        }
    }

    /**
     * Рендеринг главной страницы плагина (Конструктор)
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Advanced Forms Builder — Конструктор', 'advanced-forms-builder' ); ?></h1>
            <p>Тестирование бэкенд-слоя сохранения структуры формы (через AJAX).</p>
            
            <hr>

            <div style="background: #fff; padding: 20px; max-width: 500px; border: 1px solid #ccd0d4; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <form id="afb-admin-builder-form" onsubmit="event.preventDefault(); return false;">
                    
                    <?php wp_nonce_field( 'afb_ajax_builder_action', 'afb_ajax_nonce' ); ?>

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
        $this->render_inline_js();
    }

    /**
     * JS-код, перенаправленный на admin-ajax.php
     */
    public function render_inline_js() {
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            console.log('AFB Конструктор: AJAX-режим готов.');

            const builderForm = document.getElementById('afb-admin-builder-form');
            const responseDiv = document.getElementById('afb-builder-response');

            if (!builderForm) return;

            builderForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formTitle = document.getElementById('afb-new-form-title').value;
                const nonceVal = document.getElementById('afb_ajax_nonce') ? document.getElementById('afb_ajax_nonce').value : '';

                const mockupFields = [
                    { type: "text", name: "client_company", label: "Название компании", required: true, placeholder: "ООО Ромашка" },
                    { type: "text", name: "client_phone", label: "Номер телефона", required: true, placeholder: "+7 (999) 000-00-00" }
                ];

                responseDiv.style.display = 'block';
                responseDiv.style.backgroundColor = '#f0f0f1';
                responseDiv.style.color = '#1d2327';
                responseDiv.innerText = 'Сохранение формы...';

                // Стучимся на стандартный URL AJAX-ядра WP, подмешивая экшен в параметры строки
                fetch('ajaxurl' in window ? ajaxurl : '/wp-admin/admin-ajax.php?action=afb_save_form_builder&security=' + nonceVal, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: 0,
                        title: formTitle,
                        form_fields: mockupFields
                    })
                })
                .then(res => res.json())
                .then(resData => {
                    // WordPress AJAX возвращает структуру { success: true/false, data: { ... } }
                    if (resData.success) {
                        responseDiv.style.backgroundColor = '#edfaef';
                        responseDiv.style.color = '#00a32a';
                        responseDiv.innerHTML = `🎉 Успех! ${resData.data.message} <br>Создан Form ID: <strong>${resData.data.id}</strong>`;
                        builderForm.reset();
                    } else {
                        responseDiv.style.backgroundColor = '#fcf0f1';
                        responseDiv.style.color = '#d63638';
                        responseDiv.innerText = (resData.data && resData.data.error) ? resData.data.error : 'Ошибка сохранения.';
                    }
                })
                .catch(err => {
                    responseDiv.style.backgroundColor = '#fcf0f1';
                    responseDiv.style.color = '#d63638';
                    responseDiv.innerText = 'Сбой сети: ' + err.message;
                });
            });
        });
        </script>
        <?php
    }

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