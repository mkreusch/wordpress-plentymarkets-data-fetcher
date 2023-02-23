<?php

namespace PlentymarketsDataFetcher;

class Main
{
    const CONFIG_KEY_DOMAIN = 'pmdf_plentymarkets_domain';
    public static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->registerEvents();
    }

    public function registerEvents()
    {
        $this->addGlobalSettings();
        $this->addPlentyMarketsProxy();
        add_action('init', function(){
            wp_register_script('woocommerce_pmdf', PMDF_PLUGIN_URL . '/assets/js/plentymarkets-data.js', ['jquery']);
            wp_localize_script('woocommerce_pmdf', 'PmdfData', [
                'domain' => get_option(self::CONFIG_KEY_DOMAIN),
                'restUrl' => get_rest_url(null, 'pmdf/v1/plentymarkets-variations/')
            ]);
            wp_enqueue_script('woocommerce_pmdf');
        });

    }

    public function addPlentyMarketsProxy()
    {
        add_action('rest_api_init', function () {
            //Path to ajax search function
            register_rest_route('pmdf/v1', '/plentymarkets-variations/', [
                'methods' => 'GET',
                'callback' => function(){
                    $variationIdString = isset($_GET['variationIds'])?$_GET['variationIds']:'';
                    if($variationIdString){
                        $variationIds = array_map(function($variationId){
                            return 'variationIds[]='.(int)$variationId;
                        }, explode(',', $variationIdString));
                        $plentyUrl = 'https://'.get_option(self::CONFIG_KEY_DOMAIN).'/rest/io/variations?'.implode('&', $variationIds);
                        return json_decode(file_get_contents($plentyUrl));
                    }
                }
            ]);
        });
    }

    public function addGlobalSettings()
    {
        add_action('admin_menu', function () {
            add_options_page('plentyMarkets Data Fetcher', 'plentyMarkets Data Fetcher', 'manage_options', PMDF_PLUGIN_NAME, function () {
                ?>
                <div class="wrap">
                    <h1>My Custom Plugin Settings</h1>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields(PMDF_PLUGIN_NAME . '-settings');
                        do_settings_sections(PMDF_PLUGIN_NAME . '-settings');
                        ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="custom_text">plentyMarkets Domain</label></th>
                                <td>
                                    <input type="text" name="<?php echo self::CONFIG_KEY_DOMAIN; ?>" placeholder="mydomain.tld" id="<?php echo self::CONFIG_KEY_DOMAIN; ?>" value="<?php echo esc_attr(get_option(self::CONFIG_KEY_DOMAIN)); ?>"/>
                                </td>
                            </tr>
                        </table>
                        <?php
                        submit_button();
                        ?>
                    </form>
                </div>
                <?php
            });
        });
        add_action('admin_init', function () {
            register_setting(PMDF_PLUGIN_NAME . '-settings', self::CONFIG_KEY_DOMAIN, 'sanitize_text_field');
        });
    }
}
