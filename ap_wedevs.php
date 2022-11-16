<?php
/**
 * Plugin Name: AP Wedevs
 * Plugin URI: https://github.com/chitny/ap_wedevs
 * Description: Applicant Form plugin example, technical test for WeDevs
 * Version: 0.1
 * Author: Ignacio Chitnisky
 * Author URI: https://github.com/chitny
 * Text Domain: ap_wedevs_admin_table
 */


//When the plugin is activated for the first time "register_activation_hook" creates the table

register_activation_hook(__FILE__, 'ap_wedevs_create_db');

//When the plugin is activated "add_action" adds the plugin menu to the dashboard menu

add_action('admin_menu', 'ap_wedevs_add_menu');

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

//Require file.php to handle upload file

require_once(ABSPATH . 'wp-admin/includes/file.php'); // require file.php

//Create Table "applicant_submissions" in the database

function ap_wedevs_create_db()
{

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = 'applicant_submissions';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        firstname VARCHAR(25) NOT NULL,
        lastname VARCHAR(25) NOT NULL,
        presentaddress VARCHAR(100) NOT NULL,
        emailaddress VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        postname VARCHAR(55) NOT NULL,
        cv VARCHAR(255) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


//The "app_form_wedevs" function is the one that shows the form
// that needs to be completed on the page where the shortcode is placed

function print_app_form_wedevs($wp_error, $completed, $email_sent)
{

    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $presentaddress = $_POST['presentaddress'] ?? '';
    $emailaddress = $_POST['emailaddress'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $postname = $_POST['postname'] ?? '';
    $cvfile = $_FILES['cvfile'] ?? '';

    if ($completed) {
        $firstname = '';
        $lastname = '';
        $presentaddress = '';
        $emailaddress = '';
        $mobile = '';
        $postname = '';
        $cvfile = '';

        echo '
        <div>
            <strong>Your application has been sent, good luck.</strong>
        </div>
        ';

        if ($email_sent) {
            echo '
            <div>
            <p>We sent you a confirmation email.</p>
            </div>
            ';
        } else {
            echo 'We could not sent you a confirmation email. An unexpected error occurred.';
        }
    }

    // style of the form, to put in a css file

    echo '
            <style>
            div {
                margin-bottom:2px;
            }
            
            input:focus{
                border: 2px solid;
                border-radius: 4px;
                background-color: #E8F8F5;
            }
            input[type=button], input[type=submit], input[type=reset] {
                background-color: #04AA6D;
                border: none;
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                margin: 4px 2px;
                cursor: pointer;
                width: 60%;
              }
              form {
                width: 50%;
                margin: 0 auto;
              }
              
              label {
                text-align:left;
                width:150px;
                display:block;
                float:left;
                clear:right;
                font-size:18;
              }
              
            </style>
        ';

    // Print errors.
    if (is_wp_error($wp_error)) {
        foreach ($wp_error->get_error_messages() as $error_message) {
            echo 'Error: ' . esc_html($error_message);
        }
    }

    // print the form in the page!
    echo '
            
            <h2>Applicant Submission Form</h2>
            <p>Welcome to my Applicant Submission Form Plugin for WeDevs.</p>
            <p><strong>All fields are required.</strong> The email must be valid. You must upload a file with extension pdf, docx, doc, dot. In case there is an error, you will receive the corresponding message. If everything is correct, you will also receive a confirmation message and a confirmation email.</p>
            
            <form enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <div>
            <label for="firstname">First Name </label>
            <input type="text" name="firstname" value="' . esc_attr($firstname) . '" required>
            </div>
            
            <div>
            <label for="lastname">Last Name </label>
            <input type="text" name="lastname" value="' . esc_attr($lastname) . '" required>
            </div>
            
            <div>
            <label for="presentaddress">Present Address </label>
            <input type="text" name="presentaddress" value="' . esc_attr($presentaddress) . '" required>
            </div>
            
            <div>
            <label for="emailaddress">Email </label>
            <input type="email" name="emailaddress" value="' . esc_attr($emailaddress) . '" required>
            </div>
            
            <div>
            <label for="mobile">Mobile No </label>
            <input type="text" name="mobile" value="' . esc_attr($mobile) . '" required>
            </div>
            
            <div>
            <label for="postname">Post Name</label>
            <input type="text" name="postname" value="' . esc_attr($postname) . '" required>
            </div>
            <div>
            <label for="cvfile">CV File Upload</label>
            <input type="file" name="cvfile" value="' . (!empty($cvfile) ?
            $cvfile['name'] : '') . '" accept="application/pdf,
            application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword" required>
            </div>
            
            <input type="submit" name="submit" value="Submit"/>
            </form>
        ';
}
//This function check if all the form fields are ok and continue with the submission

function registration_validation()
{
    $reg_errors = new WP_Error;

    if (empty($_POST['firstname']) ||
        empty($_POST['lastname']) ||
        empty($_POST['presentaddress']) ||
        empty($_POST['emailaddress']) ||
        empty($_POST['mobile']) ||
        empty($_POST['postname']) ||
        empty($_FILES['cvfile']) ||
        !empty($_FILES['cvfile']['error'])
    ) {
        return new WP_Error('missing_field', 'A required form field is missing.');
    }

    if (!is_email($_POST['emailaddress'])) {
        $reg_errors->add('email_invalid', 'Email is not valid');
    }

    if (25 < strlen($_POST['firstname'])) {
        $reg_errors->add('firstname', 'First Name field must be lower than 25');
    }

    if (25 < strlen($_POST['lastname'])) {
        $reg_errors->add('lastname', 'Last Name field must be lower than 25');
    }

    if (100 < strlen($_POST['presentaddress'])) {
        $reg_errors->add('presentaddress', 'Present Address field must be lower than 100');
    }

    if (100 < strlen($_POST['emailaddress'])) {
        $reg_errors->add('emailaddress', 'Email Address field must be lower than 100');
    }

    if (20 < strlen($_POST['mobile'])) {
        $reg_errors->add('mobile', 'Mobile No field must be lower than 20');
    }

    if (55 < strlen($_POST['postname'])) {
        $reg_errors->add('postname', 'Post Name field must be lower than 55');
    }
}

//"complete_registration" If everything is correct, this function add the records to the table
function complete_registration(
    $firstname,
    $lastname,
    $presentaddress,
    $emailaddress,
    $mobile,
    $postname,
    $cvfile
) {
    global $wpdb;

    //upload file
    $uploadedfile = $cvfile; // your file input

    $upload_overrides = array('test_form' => false); // you need to do this according to docs

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides); //let it handle uploads

    $table_name = 'applicant_submissions';
    $time_stamp = date("Y-m-d H:i:s");
    $urlcv = $movefile['url'];

    $data = array(
        'time' => $time_stamp,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'presentaddress' => $presentaddress,
        'emailaddress' => $emailaddress,
        'mobile' => $mobile,
        'postname' => $postname,
        'cv' => $urlcv,
    );

    $completed = $wpdb->insert($table_name, $data);

    return $completed;
}

//IMPORTANT! this function doesnt work in localhost because wp_mail doesnt work in localhost.
function deliver_mail($emailaddress)
{
    $subject = "We received your application.";
    $message = "We received your application.";
    $meemail = get_option('admin_email');
    $headers = "From: Admin Plugin Test <$meemail>" . "\r\n";

    // If email has been process for sending, display a success message
    return wp_mail($emailaddress, $subject, $message, $headers);
}


//register the shortcode and check the form for errors and submission
function register_ap_wedevs_shortcode()
{
    $wp_error = null;
    $completed = false;
    $email_sent = false;

    if (isset($_POST['submit'])) {
        $wp_error = registration_validation();

        if (empty($wp_error)) {
            // sanitize user form input
            $firstname = sanitize_text_field($_POST['firstname']);
            $lastname = sanitize_text_field($_POST['lastname']);
            $presentaddress = sanitize_text_field($_POST['presentaddress']);
            $emailaddress = sanitize_email($_POST['emailaddress']);
            $mobile = sanitize_text_field($_POST['mobile']);
            $postname = sanitize_text_field($_POST['postname']);
            $cvfile = $_FILES['cvfile'];


            // call @function complete_registration to create the user only when no WP_error is found
            $completed = complete_registration(
                $firstname,
                $lastname,
                $presentaddress,
                $emailaddress,
                $mobile,
                $postname,
                $cvfile
            );

            if ($completed) {
                $email_sent = deliver_mail($emailaddress);
            }
        }
    }
    //create the form in the page where is the shortcode

    ob_start();
    print_app_form_wedevs($wp_error, $completed, $email_sent);
    return ob_get_clean();
}

// Extending class
class wedevsListTable extends WP_List_Table
{

    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data($search = '')
    {
        global $wpdb;

        $table = 'applicant_submissions';

        if (!empty($search)) {
            return $wpdb->get_results(
                "SELECT * from {$table} WHERE firstname Like '%{$search}%' 
                OR lastname Like '%{$search}%' 
                OR presentaddress Like '%{$search}%' 
                OR emailaddress Like '%{$search}%' 
                OR mobile Like '%{$search}%' 
                OR postname Like '%{$search}%' 
                OR cv Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT * from {$table}",
                ARRAY_A
            );
        }
    }

    // Define table columns
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'time' => __('Date', 'ap_wedevs_admin_table'),
            'firstname' => __('First Name', 'ap_wedevs_admin_table'),
            'lastname' => __('Last Name', 'ap_wedevs_admin_table'),
            'presentaddress' => __('Present Address', 'ap_wedevs_admin_table'),
            'emailaddress' => __('Email Address', 'ap_wedevs_admin_table'),
            'mobile' => __('Mobile No', 'ap_wedevs_admin_table'),
            'postname' => __('Post Name', 'ap_wedevs_admin_table'),
            'cv' => __('CV File', 'ap_wedevs_admin_table')
        );
        return $columns;
    }

    // Delete a entry record.

    public function delete_entry($id)
    {
        global $wpdb;

        $wpdb->delete(
            'applicant_submissions',
            ['ID' => $id],
            ['%d']
        );
    }

    // Bind table with columns, data and all
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = array('id');

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();
        $this->process_single_action();


        if (isset($_POST['s'])) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        usort($this->table_data, array($this, 'usort_reorder'));

        $this->items = $this->table_data;
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'time':
            case 'firstname':
            case 'lastname':
            case 'presentaddress':
            case 'emailaddress':
            case 'mobile':
            case 'postname':
            case 'cv':
            default:
                return $item[$column_name];
        }
    }

    //Fix the checkbox in every file

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['id']
        );
    }

    // let me choose what items are selected for the sort
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'lastname' => array('lastname', false),
            'time' => array('time', true)
        );
        return $sortable_columns;
    }

    // Sorting function
    public function usort_reorder($a, $b)
    {
        // If no sort, default to time
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'time';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


    // Adding action links to column
    public function column_firstname($item)
    {
        $actions = array(
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&element=%s&_wpnonce=%s">' .
                __('Delete', 'ap_wedevs_admin_table') . '</a>',
                $_REQUEST['page'],
                'delete',
                $item['id'],
                wp_create_nonce('ap-delete-single')
            ),
        );

        return sprintf('%1$s %2$s', $item['firstname'], $this->row_actions($actions));
    }

    // To show bulk action dropdown
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk_delete' => __('Delete', 'ap_wedevs_admin_table')
        );
        return $actions;
    }

    //As the name says, this function process the bulk action
    public function process_bulk_action()
    {
        $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
        if (!empty($nonce)) {
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)) {
                wp_die('You are not allowed to do this');
            }
        }

        if ('bulk_delete' === $this->current_action() && !empty($_POST['element']) && is_array($_POST['element'])) {
            $delete_ids = $_POST['element'];

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                $this->delete_entry($id);
            }
        }
    }

    //As the name says, this function process the single action
    public function process_single_action()
    {
        $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
        if (!empty($nonce)) {
            if (!wp_verify_nonce($nonce, 'ap-delete-single')) {
                wp_die('You are not allowed to do this');
            }
        }

        $id = filter_input(INPUT_GET, 'element', FILTER_SANITIZE_NUMBER_FLOAT);
        if ($id) {
            $this->delete_entry($id);
        }
    }
}

// Plugin menu callback function

function ap_wedevs_admin_page()
{
    // Creating an instance
    $table = new wedevsListTable();

    echo '<div class="wrap"><h2>WeDevs Admin Table</h2>';
    echo '<form method="post">';
    // Prepare table
    $table->prepare_items();
    // Search form
    $table->search_box('search', 'search_id');
    // Display table
    $table->display();
    echo '</div></form>';
}


//"add_shortcode" allows us to use the [applicant_form] shortcode to display the form

add_shortcode('applicant_form', 'register_ap_wedevs_shortcode');


//Admin menu setting

function ap_wedevs_add_menu()
{
    add_menu_page(
        __('AP Wedevs', 'ap_wedevs_admin_table'),
        __('AP Wedevs', 'ap_wedevs_admin_table'),
        'manage_options',
        'ap_wedevs-plugin',
        'ap_wedevs_admin_page'
    );
}


//Dashboard widget

//action Hook for the widget
add_action('wp_dashboard_setup', 'wedevs_add_widget');

//add Widget
function wedevs_add_widget()
{
    wp_add_dashboard_widget('id_wedevs_widget', 'WeDevs Example Widget', 'wedevs_show_widget');
}

//Shows the Widget
function wedevs_show_widget()
{
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    $table_name = 'applicant_submissions';
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC LIMIT 5");
    // this shows the last 5 records in the table, first the newer
    foreach ($retrieve_data as $results) {
        echo '
    <table style="width: 100%;" border="1" cellpadding="2" cellspacing="3">
        <tbody>
        <tr>
        <td width="25%">Time</td>
        <td>' . esc_html($results->time) . '</td>
        </tr>
        <td>First Name</td>
        <td>' . esc_html($results->firstname) . '</td>
        </tr>
        <td>Last Name</td>
        <td>' . esc_html($results->lastname) . '</td>
        </tr>
        <td>Present Address</td>
        <td>' . esc_html($results->presentaddress) . '</td>
        </tr>
        <td>Email</td>
        <td>' . esc_html($results->emailaddress) . '</td>
        </tr>
        <td>Mobile</td>
        <td>' . esc_html($results->mobile) . '</td>
        </tr>
        <td>Post Name</td>
        <td>' . esc_html($results->postname) . '</td>
        </tr>
        <td>CV file</td>
        <td>' . esc_html($results->cv) . '</td>
        </tr>
        </tbody>
        </table>
        <br>';
    }
}
