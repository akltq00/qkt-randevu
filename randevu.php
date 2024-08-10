<?php
/*
Plugin Name: qkt-randevu
Description: Randevu alma sistemi
Version: 1.0
Author: akltq00
*/

function appointment_booking_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        surname VARCHAR(255),
        phone VARCHAR(15),
        class_level VARCHAR(20),
        appointment_date DATE,
        appointment_time TIME
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'appointment_booking_install');

function qkt_randevusistemi() {
    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <label for="name">İsim:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="surname">Soyisim:</label>
        <input type="text" id="surname" name="surname" required><br>

        <label for="phone">Telefon Numarası:</label>
        <input type="text" id="phone" name="phone" required><br>

        <label for="class_level">Sınıf Seviyesi:</label>
        <select id="class_level" name="class_level">
            <?php for ($i = 4; $i <= 12; $i++) {
                echo "<option value='{$i}. Sınıf'>{$i}. Sınıf</option>";
            } ?>
        </select><br>

        <label for="appointment_date">Randevu Tarihi:</label>
        <input type="date" id="appointment_date" name="appointment_date" required><br>

        <label for="appointment_time">Randevu Saati:</label>
        <input type="time" id="appointment_time" name="appointment_time" required><br>

        <input type="submit" name="submit_appointment" value="Randevu Al">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('qkt-randevu', 'qkt_randevusistemi');

function handle_appointment_submission() {
    if (isset($_POST['submit_appointment'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointments';

        $name = sanitize_text_field($_POST['name']);
        $surname = sanitize_text_field($_POST['surname']);
        $phone = sanitize_text_field($_POST['phone']);
        $class_level = sanitize_text_field($_POST['class_level']);
        $appointment_date = sanitize_text_field($_POST['appointment_date']);
        $appointment_time = sanitize_text_field($_POST['appointment_time']);

        $wpdb->insert($table_name, array(
            'name' => $name,
            'surname' => $surname,
            'phone' => $phone,
            'class_level' => $class_level,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time
        ));
        echo '<div class="updated"><p>Randevu başarıyla alındı!</p></div>';
    }
}
add_action('init', 'handle_appointment_submission');

function appointment_booking_admin_menu() {
    add_menu_page('Randevu Sistemi', 'Randevu Sistemi', 'manage_options', 'appointment-booking', 'appointment_booking_admin_page', 'dashicons-calendar-alt', 6);
    add_submenu_page('appointment-booking', 'Ayarlar', 'Ayarlar', 'manage_options', 'appointment-booking-settings', 'appointment_booking_settings_page');
}
add_action('admin_menu', 'appointment_booking_admin_menu');

function appointment_booking_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';

    $results = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<h1>Randevu Listesi</h1>';
    if ($results) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>İsim</th><th>Soyisim</th><th>Telefon</th><th>Sınıf Seviyesi</th><th>Randevu Tarihi</th><th>Randevu Saati</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->name) . '</td>';
            echo '<td>' . esc_html($row->surname) . '</td>';
            echo '<td>' . esc_html($row->phone) . '</td>';
            echo '<td>' . esc_html($row->class_level) . '</td>';
            echo '<td>' . esc_html($row->appointment_date) . '</td>';
            echo '<td>' . esc_html($row->appointment_time) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Veri bulunamadı</p>';
    }
}

function appointment_booking_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_booking_roles'])) {
        $roles = array_map('sanitize_text_field', $_POST['appointment_booking_roles']);
        update_option('appointment_booking_roles', $roles);
        echo '<div class="updated"><p>Ayarlar kaydedildi!</p></div>';
    }

    $roles = get_option('appointment_booking_roles', ['administrator']);
    if (!is_array($roles)) {
        $roles = ['administrator'];
    }

    $all_roles = wp_roles()->get_names();

    echo '<h1>Randevu Sistemi Ayarları</h1>';
    echo '<form method="post">';
    echo '<h2>Görüntüleme İzinleri</h2>';
    foreach ($all_roles as $role_key => $role_name) {
        $checked = in_array($role_key, $roles) ? 'checked' : '';
        echo '<p><label><input type="checkbox" name="appointment_booking_roles[]" value="' . esc_attr($role_key) . '" ' . $checked . '> ' . esc_html($role_name) . '</label></p>';
    }
    echo '<p><input type="submit" value="Kaydet" class="button button-primary"></p>';
    echo '</form>';
}
?>
