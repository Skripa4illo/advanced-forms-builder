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
     * Динамически генерируем HTML-структуру формы из БД
     */
    private function get_form_template( $form_id ) {
        global $wpdb;
        $table_forms = $wpdb->prefix . 'afb_forms';

        // Безопасно вытягиваем строку формы из базы
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_forms} WHERE id = %d", $form_id ) );

        // Если формы с таким ID нет в базе, пишем заглушку
        if ( ! $form ) {
            echo '<p style="color:red;">Форма с ID ' . esc_html( $form_id ) . ' не найдена в системе.</p>';
            return;
        }

        // Декодируем поля из ПРАВИЛЬНОЙ колонки базы данных (form_fields)
        $fields = isset( $form->form_fields ) ? json_decode( $form->form_fields, true ) : [];

        if ( empty( $fields ) || ! is_array( $fields ) ) {
            echo '<p style="color:orange;">У формы нет настроенных полей. (Проверь имя колонки: ' . esc_html( json_encode( array_keys( (array) $form ) ) ) . ')</p>';
            return;
        }
        
        ?>
        <div class="afb-form-wrapper" id="afb-form-<?php echo $form_id; ?>">
            <h3 class="afb-form-title"><?php echo esc_html( $form->title ); ?></h3>
            
            <form class="afb-generator-form" data-form-id="<?php echo $form_id; ?>">
                <input type="hidden" class="afb-form-id-field" value="<?php echo $form_id; ?>">

                <?php 
                // Цикл по полям из базы данных
                foreach ( $fields as $field ) : 
                    $type        = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
                    $name        = isset( $field['name'] ) ? sanitize_key( $field['name'] ) : '';
                    $label       = isset( $field['label'] ) ? esc_html( $field['label'] ) : '';
                    $placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
                    $required    = ! empty( $field['required'] ) ? 'required' : '';

                    if ( empty( $name ) ) continue;
                    ?>
                    <div class="afb-form-group" style="margin-bottom: 15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">
                            <?php echo $label; ?> <?php if($required) echo '<span style="color:red;">*</span>'; ?>
                        </label>

                        <?php if ( $type === 'textarea' ) : ?>
                            <textarea 
                                name="fields[<?php echo $name; ?>]" 
                                placeholder="<?php echo $placeholder; ?>" 
                                <?php echo $required; ?> 
                                style="width:100%; padding:8px; border:1px solid #ccc; min-height:100px;"
                            ></textarea>
                        <?php else : ?>
                            <input 
                                type="<?php echo $type; ?>" 
                                name="fields[<?php echo $name; ?>]" 
                                placeholder="<?php echo $placeholder; ?>" 
                                <?php echo $required; ?> 
                                style="width:100%; padding:8px; border:1px solid #ccc;"
                            >
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="afb-submit-btn" style="padding: 10px 20px; background: #0073aa; color: #fff; border: none; cursor: pointer;">
                    Отправить форму
                </button>
                
                <div class="afb-form-response" style="margin-top: 15px; font-weight: bold;"></div>
            </form>
        </div>
        <?php
    }
}