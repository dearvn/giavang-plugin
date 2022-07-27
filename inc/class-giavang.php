<?php

/**
 * Main plugin class
 *
 * @package giavang
 */
if (!defined('ABSPATH')) {
    die();
}

/**
 * Main plugin class
 */
class Giavang {

    /**
     * The plugin directory path
     *
     * @var string
     */
    private $dirpath;

    /**
     * The plugin directory URI
     *
     * @var string
     */
    public $dirurl;

    /**
     * The plugin basename
     *
     * @var string
     */
    private $basefile;

    /**
     * The plugin basename directory
     *
     * @var string
     */
    private $basename;

    /**
     * The plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The plugin slug
     *
     * @var string
     */
    public $slug = 'giavang';

    /**
     * User meta key to hide donate notice
     *
     * @var string
     */
    public $remove_donate_notice_key = 'giavang_donate_notice_ignore';

    /**
     * Link to donate
     *
     * @var string
     */
    public $donate_link = 'https://www.buymeacoffee.com/binancashb';

    /**
     * Creates a singleton instance of the plugin class.
     */
    public static function get_instance() {
        static $inst = null;
        if (null === $inst) {
            $inst = new self();
        }
        return $inst;
    }

    /**
     * Instantiate the class
     */
    public function __construct() {
        $this->constants();
        $this->files();
        $this->hooks();
        $this->shortcodes();
    }

    /**
     * Setup dynamic properties.
     */
    public function constants() {
        $this->dirurl = plugin_dir_url(dirname(__FILE__));
        $this->dirpath = plugin_dir_path(dirname(__FILE__));
        $this->basefile = plugin_basename($this->dirpath . '/' . $this->slug . '.php');
        $this->basename = basename($this->dirpath);
        $this->settings_page_url = admin_url('options-general.php?page=' . $this->slug);
    }

    /**
     * Include additional files
     */
    public function files() {
        // Widget class.
        require_once trailingslashit($this->dirpath) . 'inc/widgets/class-giavang-widget.php';
    }

    /**
     * Register any actions or filters the plugin needs
     */
    public function hooks() {
        // Actions.
        add_action('admin_enqueue_scripts', array($this, 'admin_resources'));
        add_action('admin_init', array($this, 'remove_donate_notice_nojs'));
        add_action('wp_ajax_giavang_remove_donate_notice', array($this, 'remove_donate_notice'));
        add_action('init', array($this, 'register_assets'));
        add_action('widgets_init', array($this, 'load_widget'));
        // Filters.
        add_filter('widget_text', 'do_shortcode');
        add_filter('plugin_action_links_' . $this->basename, array($this, 'plugin_action_links'));
    }

    /**
     * Register the plugins shortcode(s)
     */
    public function shortcodes() {
        add_shortcode('giavang', array($this, 'giavang'));
    }

    /**
     * Runs on activation
     *
     * @param string $plugin The filename of the plugin including the path.
     */
    public function activate($plugin) {
        if ($plugin === $this->basefile) {
            wp_safe_redirect(admin_url('options-general.php?page=giavang'));
        }
    }

    /**
     * Generate the markup for the donate notice
     *
     * @param bool $echo Return or echo the markup.
     * @return bool|void
     */
    public function donate_notice($echo = false) {
        $return = null;

        if (current_user_can('administrator')) {
            $user_id = get_current_user_id();

            if (!get_user_meta($user_id, $this->remove_donate_notice_key) || get_user_meta($user_id, $this->remove_donate_notice_key) === false) {
                $return .= '<div class="giavang-donate"><p>';
                $return .= __('Thank you for using the Giavang. Please consider donating to support ongoing development. ', 'giavang');
                $return .= '</p><p>';
                $return .= '<a href="' . $this->donate_link . '" target="_blank" class="button button-secondary">' . __('Donate now', 'giavang') . '</a>';
                $return .= '<a href="?' . $this->remove_donate_notice_key . '=0" class="notice-dismiss giavang-donate-notice-dismiss" title="' . __('Dismiss this notice', 'giavang') . '"><span class="screen-reader-text">' . __('Dismiss this notice', 'giavang') . '.</span></a>';
                $return .= '</p></div>';
            }
        }

        if ($echo) {
            echo $return;
        } else {
            return $return;
        }
    }

    /**
     * Set a user meta key to prevent the donate nag showing
     */
    public function remove_donate_notice() {
        $user_id = get_current_user_id();
        update_user_meta($user_id, $this->remove_donate_notice_key, 'true', true);

        if (wp_doing_ajax()) {
            wp_die();
        }
    }

    /**
     * No JS callback for removing the donate notice
     */
    public function remove_donate_notice_nojs() {
        if (isset($_GET[$this->remove_donate_notice_key]) && 0 === absint($_GET[$this->remove_donate_notice_key])) {
            $this->remove_donate_notice();
        }
    }

    /**
     * Add a link to support on plugins listing
     *
     * @param array $links Array of links.
     * @return array
     */
    public function plugin_action_links($links) {
        $links[] = sprintf(
                '<a href="https://wordpress.org/support/plugin/giavang" target="_blank">%1$s</a>',
                __('Support', 'giavang')
        );
        return $links;
    }

    /**
     * Register CSS and JS assets
     */
    public function register_assets() {
        // Styles.
        wp_register_style('giavang-css', trailingslashit($this->dirurl) . 'css/giavang.css', array(), $this->version);
        wp_register_style('giavang-awesome-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version);
    }

    /**
     * Enqueue CSS and JS for admin
     */
    public function admin_resources() {
        wp_enqueue_style('giavang-admin-styles');
    }

    /**
     * Create a random string to serve as the ID of the wrapper
     */
    public function giavang_generate_wrapper_id() {
        return wp_generate_password(15, false);
    }

    /**
     * Parse shortcode
     *
     * @param array $filter Supplied shortcode attributes.
     * @return string
     */
    public function giavang($filter) {
        wp_enqueue_style('giavang-css');
        wp_enqueue_style('giavang-awesome-fonts');
        $return = '';
        $a = shortcode_atts(
                array(
                    'gold' => null,
                    'dollar' => null,
                    '_implementation' => 'shortcode',
                ),
                $filter
        );

        if (isset($a['gold']) && !empty($a['gold'])) {

            $return .= sprintf(
                    '<div class="giavang-gold" data-version="%1$s" data-implementation="%2$s" id="%3$s" data-gold="%4$s">',
                    esc_attr($this->version),
                    esc_attr($a['_implementation']),
                    esc_attr($this->giavang_generate_wrapper_id()),
                    esc_attr($a['gold'])
            );

            $return .= self::load_data_gold();

            $return .= '</div>'; // .dearvn_giavang.
        }

        if (isset($a['dollar']) && !empty($a['dollar'])) {

            $return .= sprintf(
                    '<div class="giavang-dollar" data-version="%1$s" data-implementation="%2$s" id="%3$s" data-dollar="%4$s">',
                    esc_attr($this->version),
                    esc_attr($a['_implementation']),
                    esc_attr($this->giavang_generate_wrapper_id()),
                    esc_attr($a['dollar'])
            );

            $return .= self::load_data_dollar();

            $return .= '</div>'; // .dearvn_giavang.
        }
        return $return;
    }

    private function load_data_gold() {
        $resp = wp_remote_get('https://mihong.vn/api/v1/gold/prices/current');
        if (!empty($resp)) {
            $body = wp_remote_retrieve_body($resp);
            $obj = json_decode($body);
            $datas = (array) $obj->data;

            $str = "<div class='giavang-table'><div class='giavang-table-caption'>"
                    . "<div class='giavang-table-header'><div class='table-header-cell'>"
                    . "</div><div class='giavang-table-header-cell'>Mua(tr/cây)</div>"
                    . "<div class='giavang-table-header-cell'>Bán(tr/cây)</div></div><div class='giavang-table-body'>";
            foreach ($datas as $data) {
                $buy_price = number_format($data->buyingPrice * 10 / 1000);
                $buy_change = $data->buyChange * 10 / 1000;
                $buy_changePct = $data->buyChangePercent;
                $buy_up = $buy_change > 0 ? 'giavang-up' : 'giavang-down';
                $buy_up_icon = $buy_change > 0 ? '<i class="fa fa-caret-up"></i>' : '<i class="fa fa-caret-down"></i>';
                $sell_price = number_format($data->sellingPrice * 10 / 1000);
                $sell_change = $data->sellChange * 10 / 1000;
                $sell_changePct = $data->sellChangePercent;
                $sell_up = $sell_change > 0 ? 'giavang-up' : 'giavang-down';
                $sell_up_icon = $sell_change > 0 ? '<i class="fa fa-caret-up"></i>' : '<i class="fa fa-caret-down"></i>';

                $str .= "<div class='giavang-table-row'>"
                        . "<div class='giavang-table-body-cell'>{$data->code}</div>"
                        . "<div class='giavang-table-body-cell'>{$buy_price}<br/><span class='giavang-small {$buy_up}'>{$buy_up_icon} {$buy_change}({$buy_changePct}%)</span></div>"
                        . "<div class='giavang-table-body-cell'>{$sell_price}<br/><span class='giavang-small {$sell_up}'>{$sell_up_icon} {$sell_change}({$sell_changePct}%)</span></div>"
                        . "</div>";
            }
            $str .= "</div></div></div>";
        }

        return $str;
    }

    private function load_data_dollar() {
        $resp = wp_remote_get('https://mihong.vn/api/v1/currency/current');
        if (!empty($resp)) {
            $body = wp_remote_retrieve_body($resp);
            $obj = json_decode($body);
            $datas = (array) $obj->data;

            $str = "<div class='giavang-table'><div class='giavang-table-caption'>"
                    . "<div class='giavang-table-header'><div class='table-header-cell'>"
                    . "</div><div class='giavang-table-header-cell'>Ngân Hàng</div>"
                    . "<div class='giavang-table-header-cell'>Tự Do</div></div><div class='giavang-table-body'>";
            foreach ($datas as $data) {
                $bank_price = number_format($data->viettin->buyingPrice);
                $bank_change = $data->viettin->buyChange;
                $bank_up = $bank_change > 0 ? 'giavang-up' : 'giavang-down';
                $bank_up_icon = $bank_change > 0 ? '<i class="fa fa-caret-up"></i>' : '<i class="fa fa-caret-down"></i>';

                $market_price = number_format($data->mihong->buyingPrice);
                $market_change = $data->mihong->buyChange;
                $market_up = $market_change > 0 ? 'giavang-up' : 'giavang-down';
                $market_up_icon = $market_change > 0 ? '<i class="fa fa-caret-up"></i>' : '<i class="fa fa-caret-down"></i>';

                $str .= "<div class='giavang-table-row'>"
                        . "<div class='giavang-table-body-cell'>{$data->mihong->code}</div>"
                        . "<div class='giavang-table-body-cell'>{$bank_price}<br/><span class='giavang-small {$bank_up}'>{$bank_up_icon} {$bank_change}</span></div>"
                        . "<div class='giavang-table-body-cell'>{$market_price}<br/><span class='giavang-small {$market_up}'>{$market_up_icon} {$market_change}</span></div>"
                        . "</div>";
            }
            $str .= "</div></div></div>";
        }

        return $str;
    }

    /**
     * Register the widget
     */
    public function load_widget() {
        register_widget('Giavang_Widget');
    }

    /**
     * Get settings for the embed
     *
     * @return array
     */
    public function get_settings() {
        $return = array();
        return $return;
    }

}
