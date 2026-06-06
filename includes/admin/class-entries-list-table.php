<?php
namespace AFB\Admin;

// WP_List_Table не подгружается автоматически, подключаем ядро движка вручную
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Class_Entries_List_Table extends \WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'afb-entry', // ID одной записи
            'plural'   => 'afb-entries', // ID списка записей
            'ajax'     => false,
        ] );
    }

    /**
     * Определяем колонки нашей таблицы
     */
    public function get_columns() {
        return [
            'id'         => __( 'ID', 'advanced-forms-builder' ),
            'form_id'    => __( 'ID Формы', 'advanced-forms-builder' ),
            'response'   => __( 'Данные формы (JSON / Ответы)', 'advanced-forms-builder' ),
            'user_ip'    => __( 'IP Пользователя', 'advanced-forms-builder' ),
            'created_at' => __( 'Дата отправки', 'advanced-forms-builder' ),
        ];
    }

    /**
     * Указываем, по каким колонкам разрешена сортировка
     */
    protected function get_sortable_columns() {
        return [
            'id'         => [ 'id', true ],
            'created_at' => [ 'created_at', false ],
        ];
    }

    /**
     * Дефолтный рендеринг для колонок (если нет кастомного метода)
     */
    public function column_default( $item, $column_name ) {
        return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
    }

    /**
     * Кастомный рендеринг для колонки "response" — превращаем сырой JSON в читаемый вид
     */
    public function column_response( $item ) {
        $data = json_decode( $item['response'], true );
        if ( empty( $data ) || ! is_array( $data ) ) {
            return '<em style="color:#999;">Пустой ответ</em>';
        }

        $output = '<ul style="margin: 0; padding-left: 15px; list-style-type: disc;">';
        foreach ( $data as $key => $value ) {
            // Санитизируем вывод ключей и значений
            $label = esc_html( $key );
            $val   = is_array( $value ) ? esc_html( implode( ', ', $value ) ) : esc_html( $value );
            
            $output .= sprintf( '<li><strong>%s</strong>: %s</li>', $label, $val );
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * Основной метод подготовки данных (выборка, сортировка, пагинация)
     */
    public function prepare_items() {
        global $wpdb;
        $table_entries = $wpdb->prefix . 'afb_entries';

        // 1. Настраиваем пагинацию
        $per_page     = 10;
        $current_page = $this->get_pagenum();

        // 2. Обрабатываем сортировку (Order & OrderBy)
        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'id';
        $order   = isset( $_GET['order'] ) && in_array( strtolower( $_GET['order'] ), [ 'asc', 'desc' ] ) ? strtolower( $_GET['order'] ) : 'desc';

        // 3. Считаем общее количество записей
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM {$table_entries}" );

        // 4. Делаем безопасный SQL-запрос с учетом пагинации и сортировки
        $offset = ( $current_page - 1 ) * $per_page;
        
        // Разрешаем сортировку только по валидным колонкам, чтобы избежать SQL-injection
        $allowed_orderby = [ 'id', 'created_at' ];
        if ( ! in_array( $orderby, $allowed_orderby ) ) {
            $orderby = 'id';
        }

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_entries} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $this->items = $wpdb->get_results( $query, ARRAY_A );

        // 5. Регистрируем мета-данные пагинации в ядре WordPress
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ] );

        // Настраиваем заголовки колонок
        $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
    }
}