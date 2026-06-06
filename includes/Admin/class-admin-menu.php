<?php
namespace AFB\Admin;

class Class_Admin_Menu {

    public function __construct() {
        // Хук admin_menu инициализируется вовремя, оставляем его здесь
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
    }

    /**
     * Регистрируем страницы меню
     */
    public function add_menu_pages() {
        // Главная страница плагина (Конструктор)
        $main_page = add_menu_page(
            __( 'AFB Forms', 'advanced-forms-builder' ),
            __( 'AFB Forms', 'advanced-forms-builder' ),
            'manage_options',
            'afb-forms',
            [ $this, 'render_admin_page' ],
            'dashicons-feedback',
            30
        );

        // Дочерняя страница (Заявки)
        $sub_page = add_submenu_page(
            'afb-forms', 
            __( 'Заявки', 'advanced-forms-builder' ),
            __( 'Заявки', 'advanced-forms-builder' ),
            'manage_options',
            'afb-entries', 
            [ $this, 'render_entries_page' ] 
        );

        // Используем load-{$page_handle} — это железобетонный способ сказать WP:
        // "Подключи скрипты только тогда, когда админ зашел именно на эту страницу"
        add_action( "load-{$main_page}", [ $this, 'register_builder_assets' ] );
        add_action( "load-{$sub_page}", [ $this, 'register_builder_assets' ] );
    }

    /**
     * Прослойка, которая гарантирует вызов admin_enqueue_scripts в правильный момент времени
     */
    public function register_builder_assets() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Подключаем скрипты для админки плагина
     */
    public function enqueue_admin_assets( $hook ) {
        // На всякий случай выведем хук в консоль, чтобы убедиться, что всё завелось
        echo "<script>console.log('AFB Проверка: Скрипты успешно подключены на хуке: " . esc_js( $hook ) . "');</script>";

        // Системный скрипт WP, который создаст window.wpApiSettings с актуальным nonce
        wp_enqueue_script( 'wp-api-js' );

        // Вычисляем точный URL к папке assets/js относительно текущего файла
        $plugin_root_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
        $js_file_url     = $plugin_root_url . 'assets/js/admin-builder.js';

        wp_enqueue_script(
            'afb-admin-builder-script',
            $js_file_url,
            [ 'jquery', 'wp-api-js' ],
            time(), // Сброс кэша Hostinger при каждом обновлении страницы
            true
        );
    }

    /**
     * Рендеринг главной страницы плагина (Конструктор)
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Advanced Forms Builder — Конструктор', 'advanced-forms-builder' ); ?></h1>
            <p>Тестирование бэкенд-слоя сохранения структуры формы.</p>
            
            <hr>

            <div style="background: #fff; padding: 20px; max-width: 500px; border: 1px solid #ccd0d4; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <form id="afb-admin-builder-form" onsubmit="event.preventDefault(); return false;">
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