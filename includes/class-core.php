<?php
namespace AFB;

class Class_Core {

    public function run() {
        $this->load_dependencies();
        
        // Регистрируем фронтенд скрипты через правильный хук WP
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    private function load_dependencies() {
        require_once AFB_PATH . 'includes/frontend/class-form-render.php';
        require_once AFB_PATH . 'includes/frontend/class-form-handler.php';
        require_once AFB_PATH . 'includes/frontend/class-form-block.php';
        require_once AFB_PATH . 'includes/admin/class-entries-list-table.php';
        
        if ( is_admin() ) {
            new Admin\Class_Admin_Menu();
        }

        new \AFB\frontend\Class_Form_Render();
        new \AFB\frontend\Class_Form_Handler();
        new \AFB\frontend\Class_Form_Block();
    }

    /**
     * Подключаем JS-скрипты для фронтенда сайта
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_script(
            'afb-frontend-form-script',
            AFB_URL . 'assets/js/frontend-form.js',
            [],
            time(), // Временно time() для сброса кэша Hostinger при деплое
            true   // Жестко грузим в подвале (footer), чтобы DOM успел собраться
        );
    }
}