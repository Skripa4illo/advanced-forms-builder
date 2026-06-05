<?php
namespace AFB;

class Class_Core {

	public function run() {
		$this->load_dependencies();
	}

	private function load_dependencies() {
		if ( is_admin() ) {
			new Admin\Class_Admin_Menu();
		}

		// Запускаем рендеринг шорткодов
		new Frontend\Class_Form_Render();

		// Инициализируем обработчик API стандартным для WP способом
		new Frontend\Class_Form_Handler();

		// Инициализируем сбор формы блоками на фронте
        new Frontend\Class_Form_Block();
	}
}