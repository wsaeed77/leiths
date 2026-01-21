<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Rest extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("REST API", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $encryptKey = $instance->getConfig("android_encrypt_key");

        $this->addGroup( "default", __( "REST API", "wpjobboard" ) );
        
        $e = $this->create("android_encrypt_key");
        $e->setRequired(true);
        $e->setLabel(__("API Encryption Key", "wpjobboard"));
        $e->setValue($encryptKey);
        $e->setRenderer(array($this, "randomize"));
        $e->setOrder(7);
        $this->addElement($e, "default");



        

        if( $encryptKey ) {
            $this->consts();

            $e = $this->create("_android_api_users");
            $e->setLabel(__("API Users", "wpjobboard"));
            $e->setValue("");
            $e->setRenderer(array($this, "users"));
            $e->setOrder(10);
            $this->addElement($e, "default");
        }
        
        apply_filters("wpja_form_init_config_android", $this);

    }
    
    public function consts() {

        $e = $this->create("android_site_url_home");
        $e->setLabel(__("API URL - Home", "wpjobboard"));
        $e->setValue(home_url());
        $e->setAttr("readonly", "readonly");
        $e->setOrder(5);
        $this->addElement($e, "default");

        $e = $this->create("android_site_url");
        $e->setLabel(__("API URL", "wpjobboard"));
        $e->setValue( rtrim( home_url(), "/" ) . "/wpjobboard" );
        $e->setAttr("readonly", "readonly");
        $e->setOrder(6);
        $this->addElement($e, "default");

        $s = new Daq_Helper_Security();

        $e = $this->create("android_site_cypher");
        $e->setLabel(__("API Cypher", "wpjobboard"));
        $e->setValue( $s->get_algo() );
        $e->setAttr("readonly", "readonly");
        $e->setOrder(7);
        $this->addElement($e, "default");
    }

    public function isValid(array $values)
    {
        $request = Daq_Request::getInstance();
        
        if($request->post("generate")) {
            $values["android_encrypt_key"] = md5( time() . "-" . home_url() . NONCE_KEY );
        }
        if($request->post("generate-user")) {
            $this->generateUser();
        }

        return parent::isValid($values);
         
    }

    public function generateUser() {


        $username = "rest-api";
        $index = 2;

        do {
            $user_id = wp_create_user( $username, md5( time() ) );
            $username = sprintf( "rest-api-%d", $index );
            $index++;
        } while( $user_id instanceof WP_Error );
        
        $user = get_user_by( "ID", $user_id );

        $token = sha1(time()."/".uniqid()."/".$user->ID."/".rand(0,90000));        
        
        $user->remove_all_caps();
        $user->add_cap( "import", true );
        
        add_user_meta($user->ID, "wpjb_access_token", $token, true);
    }
    
    public function randomize($field, $form) {
        if($field->getValue() == "") {
            $html = new Daq_Helper_Html("input", array(
                "type" => "submit",
                "name" => "generate",
                "class" => "button-secondary",
                "value" =>  __("Generate", "wpjobboard")
            ));
            $html->forceLongClosing(false);
            return $html;
        } else {
            return $field->render();
        }
    }

    public function users($field, $form) {

        $html = new Daq_Helper_Html("input", array(
            "type" => "submit",
            "name" => "generate-user",
            "class" => "button-secondary",
            "value" =>  __("+ Add New", "wpjobboard")
        ));
        $html->forceLongClosing(false);

        $html2 = $this->usersTable();


        return $html . $html2;
    }

    public function usersTable() {

        $users = get_users(array(
            'meta_key' => 'wpjb_access_token',
            'meta_value' => NULL,
            'meta_compare' => 'EXISTS'
        ));

        if( empty( $users ) ) {
            return '<div style="padding:10px 0 10px 0">'. __( "No API users added yet.", "wpjobboard" ).'</div>';
            return;
        }

        ob_start();
        ?>
        <div>&nbsp;</div>
        <table>
            <thead>
                <tr>
                    <th style="padding:10px 15px 10px 15px"><?php _e("Username", "wpjobboard" ) ?></th>
                    <th style="padding:10px 15px 10px 15px"><?php _e("API Access Token", "wpjobboard" ) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $users as $user ): ?>
                <tr>
                    <td><a href="<?php echo esc_url( admin_url( sprintf( "users.php?user_id=%d", $user->ID ) ) ) ?> "><?php echo esc_html( $user->user_login ) ?></a></td>
                    <td><code><?php echo esc_html( get_user_meta( $user->ID, "wpjb_access_token", true ) ) ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}

?>