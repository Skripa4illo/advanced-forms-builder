<?php
namespace AFB;

class Class_Core {

	public function run() {
		$this->load_dependencies();
	}

	private function load_dependencies() {
		// ЖЕСТКО ПОДКЛЮЧАЕМ ФАЙЛ БЛОКА В ОБХОД АВТОЗАГРУЗЧИКА
    	require_once AFB_PATH . 'includes/frontend/class-form-block.php';
		
		if ( is_admin() ) {
			new Admin\Class_Admin_Menu();
		}

		// Запускаем рендеринг шорткодов
		new frontend\Class_Form_Render();

		// Инициализируем обработчик API
		new frontend\Class_Form_Handler();

		// Инициализируем сбор формы блоками
		new frontend\Class_Form_Block();
	}
}