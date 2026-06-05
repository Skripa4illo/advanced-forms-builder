<?php
namespace AFB\frontend;

class Class_Form_Render {

    public function __construct() {
        // Регистрируем шорткод [afb_form id="1"]
        // Если он уже зарегистрирован где-то, WP просто обновит колбэк
        if ( ! shortcode_exists( 'afb_form' ) ) {
            add_shortcode( 'afb_form', [ $this, 'render_form_shortcode' ] );
        }
    }

    /**
     * Колбэк для шорткода и для Гутенберг-блока
     */
    public function render_form_shortcode( $atts ) {
        // Парсим атрибуты, задаем дефолтный ID формы = 1
        $args = shortcode_atts( [
            'id' => 1,
        ], $atts, 'afb_form' );

        $form_id = absint( $args['id'] );

        // Включаем буферизацию, чтобы HTML не выплескивался в случайном месте страницы
        ob_start();
        
        $this->get_form_template( $form_id );

        return ob_get_clean();
    }

    /**
     * Генерируем HTML-структуру формы
     */
    private function get_form_template( $form_id ) {
        ?>
        <div class="afb-form-wrapper" id="afb-form-<?php echo $form_id; ?>">
            <form class="afb-generator-form" data-form-id="<?php echo $form_id; ?>">
                
                <input type="hidden" class="afb-form-id-field" value="<?php echo $form_id; ?>">

                <div class="afb-form-group" style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Ваше имя:</label>
                    <input type="text" name="fields[user_name]" required style="width:100%; padding:8px; border:1px solid #ccc;">
                </div>

                <div class="afb-form-group" style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Email:</label>
                    <input type="email" name="fields[user_email]" required style="width:100%; padding:8px; border:1px solid #ccc;">
                </div>

                <button type="submit" class="afb-submit-btn" style="padding: 10px 20px; background: #0073aa; color: #fff; border: none; cursor: pointer;">
                    Отправить форму (ID: <?php echo $form_id; ?>)
                </button>
                
                <div class="afb-form-response" style="margin-top: 15px; font-weight: bold;"></div>
            </form>
        </div>
        <?php
    }
}