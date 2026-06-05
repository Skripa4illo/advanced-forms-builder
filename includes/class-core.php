<?php
namespace AFB;

class Class_Core {

    public function run() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        // Жестко подключаем файлы (пока автозагрузчик отдыхает на подстраховке)
        require_once AFB_PATH . 'includes/frontend/class-form-render.php';
        require_once AFB_PATH . 'includes/frontend/class-form-handler.php';
        require_once AFB_PATH . 'includes/frontend/class-form-block.php';
        
        if ( is_admin() ) {
            new Admin\Class_Admin_Menu();
        }

        // Инициализируем компоненты АБСОЛЮТНО жестко от корня неймспейса
        new \AFB\frontend\Class_Form_Render();
        new \AFB\frontend\Class_Form_Handler();
        new \AFB\frontend\Class_Form_Block();
    }
}