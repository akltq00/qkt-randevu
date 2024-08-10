<?php
/*
Plugin Name: qkt-randevu
Description: Randevu alma sistemi
Version: 2.8
Author: akltq00
*/

function appointment_booking_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_name VARCHAR(255),
        parent_surname VARCHAR(255),
        student_name VARCHAR(255),
        student_surname VARCHAR(255),
        phone VARCHAR(15),
        class_level VARCHAR(20),
        appointment_date DATE,
        appointment_time TIME
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'appointment_booking_install');

function appointment_booking_shortcode() {
    ob_start();
    ?>
    <style>
        .appointment-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .appointment-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .appointment-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .appointment-form input,
        .appointment-form select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .appointment-form input[type="submit"] {
            width: 100%;
            background-color: #0073aa;
            color: white;
            border: none;
            cursor: pointer;
            padding: 15px;
            font-size: 16px;
        }
        .appointment-form input[type="submit"]:hover {
            background-color: #005177;
        }
    </style>
    <form class="appointment-form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <h2>Randevu Al</h2>
        <label for="parent_name">Veli İsim:</label>
        <input type="text" id="parent_name" name="parent_name" required>

        <label for="parent_surname">Veli Soyisim:</label>
        <input type="text" id="parent_surname" name="parent_surname" required>

        <label for="student_name">Öğrenci İsim:</label>
        <input type="text" id="student_name" name="student_name" required>

        <label for="student_surname">Öğrenci Soyisim:</label>
        <input type="text" id="student_surname" name="student_surname" required>

        <label for="phone">Telefon Numarası:</label>
        <input type="text" id="phone" name="phone" required>

        <label for="class_level">Sınıf Seviyesi:</label>
        <select id="class_level" name="class_level">
            <?php for ($i = 4; $i <= 12; $i++) {
                echo "<option value='{$i}. Sınıf'>{$i}. Sınıf</option>";
            } ?>
        </select>

        <label for="appointment_date">Randevu Tarihi (gg.aa.yyyy):</label>
        <input type="text" id="appointment_date" name="appointment_date" placeholder="gg.aa.yyyy" required pattern="\d{2}\.\d{2}\.\d{4}">

        <label for="appointment_time">Randevu Saati:</label>
        <input type="time" id="appointment_time" name="appointment_time" required>

        <input type="submit" name="submit_appointment" value="Randevu Al">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('qkt-randevu', 'appointment_booking_shortcode');

function handle_appointment_submission() {
    if (isset($_POST['submit_appointment'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointments';

        $parent_name = sanitize_text_field($_POST['parent_name']);
        $parent_surname = sanitize_text_field($_POST['parent_surname']);
        $student_name = sanitize_text_field($_POST['student_name']);
        $student_surname = sanitize_text_field($_POST['student_surname']);
        $phone = sanitize_text_field($_POST['phone']);
        $class_level = sanitize_text_field($_POST['class_level']);
        $appointment_date_raw = sanitize_text_field($_POST['appointment_date']);
        $appointment_time = sanitize_text_field($_POST['appointment_time']);

        $appointment_date = DateTime::createFromFormat('d.m.Y', $appointment_date_raw)->format('Y-m-d');

        $wpdb->insert($table_name, array(
            'parent_name' => $parent_name,
            'parent_surname' => $parent_surname,
            'student_name' => $student_name,
            'student_surname' => $student_surname,
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
        echo '<thead><tr><th>Veli İsim</th><th>Veli Soyisim</th><th>Öğrenci İsim</th><th>Öğrenci Soyisim</th><th>Telefon</th><th>Sınıf Seviyesi</th><th>Randevu Tarihi</th><th>Randevu Saati</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->parent_name) . '</td>';
            echo '<td>' . esc_html($row->parent_surname) . '</td>';
            echo '<td>' . esc_html($row->student_name) . '</td>';
            echo '<td>' . esc_html($row->student_surname) . '</td>';
            echo '<td>' . esc_html($row->phone) . '</td>';
            echo '<td>' . esc_html($row->class_level) . '</td>';
            echo '<td>' . esc_html(date('d.m.Y', strtotime($row->appointment_date))) . '</td>';
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
    echo '<p><input type="submit" class="button button-primary" value="Kaydet"></p>';
    echo '</form>';
}
?>
