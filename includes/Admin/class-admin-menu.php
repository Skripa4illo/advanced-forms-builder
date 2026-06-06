<?php
namespace AFB\Admin;

class Class_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
    }

    public function add_menu_pages() {
        // Главная страница плагина
        add_menu_page(
            __( 'AFB Forms', 'advanced-forms-builder' ),
            __( 'AFB Forms', 'advanced-forms-builder' ),
            'manage_options',
            'afb-forms',
            [ $this, 'render_admin_page' ],
            'dashicons-feedback',
            30
        );

        // Дочерняя страница: Список заявок (Entries)
        add_submenu_page(
            'afb-forms', // Родитеский слаг меню
            __( 'Заявки', 'advanced-forms-builder' ),
            __( 'Заявки', 'advanced-forms-builder' ),
            'manage_options',
            'afb-entries', // Слаг этой подстраницы
            [ $this, 'render_entries_page' ] // Колбэк рендеринга
        );
    }

    /**
     * Рендеринг главной страницы плагина (Конструктор)
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Advanced Forms Builder', 'advanced-forms-builder' ); ?></h1>
            <p><?php _e( 'Добро пожаловать. Здесь будет Vue.js/React конструктор форм.', 'advanced-forms-builder' ); ?></p>
        </div>
        <?php
    }

    /**
     * Рендеринг страницы со списком заявок через WP_List_Table
     */
    public function render_entries_page() {
        // Подключаем наш класс таблицы (Убедись, что файл ядра core.php делает require этого файла)
        require_once AFB_PATH . 'includes/admin/class-entries-list-table.php';

        $entries_table = new Class_Entries_List_Table();
        $entries_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Заявки из форм (Entries)', 'advanced-forms-builder' ); ?></h1>
            <hr class="wp-header-end">

            <form method="get">
                <input type="hidden" name="page" value="afb-entries" />
                <?php
                // Выводим саму таблицу
                $entries_table->display();
                ?>
            </form>
        </div>
        <?php
    }
}