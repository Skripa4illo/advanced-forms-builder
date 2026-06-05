<?php
namespace AFB;

class Class_Core {

    public function run() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        // Жестко подключаем файл
        require_once AFB_PATH . 'includes/frontend/class-form-block.php';
        
        if ( is_admin() ) {
            new Admin\Class_Admin_Menu();
        }

        // Запускаем рендеринг шорткодов (тоже через жесткое подключение если надо, но пока оставим так)
        new frontend\Class_Form_Render();
        new frontend\Class_Form_Handler();

        // Инициализируем сбор формы блоками — ЖЕСТКО УКАЗЫВАЕМ ПОЛНЫЙ НЕЙМСПЕЙС ОТ КОРНЯ
        new \AFB\frontend\Class_Form_Block();
    }
}