<?php
/*
Plugin Name: ChatWP
Description: A WordPress plugin that displays text from OpenAI's Completions API. Enter your OpenAI API key in the settings and then add as many prompt-specific widget instances as you'd like! Each widget instance can be configured with its own prompt and OpenAI configs.
Version: 1.0
Author: Adam Moro
Author URI: https://adammoro.com/
*/

// Add widget to display completion text
class ChatWP_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'chatwp_widget', // Base ID
            'ChatWP Widget', // Name
            array('description' => __('A widget that displays text from OpenAI\'s Completions API.', 'chatwp'),) // Args
        );
    }

    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        $prompt = $instance['prompt'];
        $model = $instance['model'];
        $temperature = $instance['temperature'];
        $max_tokens = $instance['max_tokens'];
        $frequency_penalty = $instance['frequency_penalty'];
        $presence_penalty = $instance['presence_penalty'];
        $api_key = get_option('chatwp_api_key');
        $response = chatwp_get_completion($api_key, $prompt, $model, $temperature, $max_tokens, $frequency_penalty, $presence_penalty);
        $text = '';
        if ($response && isset($response->choices)) {
            $text = $response->choices[0]->text;
        }
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo '<div class="chatwp-text">' . $text . '</div>';
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : '';
        $prompt = isset($instance['prompt']) ? $instance['prompt'] : 'How many owls does it take to get to the center of a sparrow nest?';
        $model = isset($instance['model']) ? $instance['model'] : 'text-davinci-003';
        $temperature = isset($instance['temperature']) ? $instance['temperature'] : '0.9';
        $max_tokens = isset($instance['max_tokens']) ? $instance['max_tokens'] : '250';
        $frequency_penalty = isset($instance['frequency_penalty']) ? $instance['frequency_penalty'] : '0';
        $presence_penalty = isset($instance['presence_penalty']) ? $instance['presence_penalty'] : '0.6';
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('prompt'); ?>"><?php _e('Prompt:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('prompt'); ?>" name="<?php echo $this->get_field_name('prompt'); ?>" type="text" value="<?php echo esc_attr($prompt); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('model'); ?>"><?php _e('Model:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('model'); ?>" name="<?php echo $this->get_field_name('model'); ?>" type="text" value="<?php echo esc_attr($model); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('temperature'); ?>"><?php _e('Temperature:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('temperature'); ?>" name="<?php echo $this->get_field_name('temperature'); ?>" type="text" value="<?php echo esc_attr($temperature); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('max_tokens'); ?>"><?php _e('Max Tokens:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('max_tokens'); ?>" name="<?php echo $this->get_field_name('max_tokens'); ?>" type="text" value="<?php echo esc_attr($max_tokens); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('frequency_penalty'); ?>"><?php _e('Frequency Penalty:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('frequency_penalty'); ?>" name="<?php echo $this->get_field_name('frequency_penalty'); ?>" type="text" value="<?php echo esc_attr($frequency_penalty); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('presence_penalty'); ?>"><?php _e('Presence Penalty:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('presence_penalty'); ?>" name="<?php echo $this->get_field_name('presence_penalty'); ?>" type="text" value="<?php echo esc_attr($presence_penalty); ?>">
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['prompt'] = (!empty($new_instance['prompt'])) ? strip_tags($new_instance['prompt']) : '';
        $instance['model'] = (!empty($new_instance['model'])) ? strip_tags($new_instance['model']) : '';
        $instance['temperature'] = (!empty($new_instance['temperature'])) ? strip_tags($new_instance['temperature']) : '';
        $instance['max_tokens'] = (!empty($new_instance['max_tokens'])) ? strip_tags($new_instance['max_tokens']) : '';
        $instance['frequency_penalty'] = (!empty($new_instance['frequency_penalty'])) ? strip_tags($new_instance['frequency_penalty']) : '';
        $instance['presence_penalty'] = (!empty($new_instance['presence_penalty'])) ? strip_tags($new_instance['presence_penalty']) : '';
        return $instance;
    }
}

// Register ChatWP_Widget widget
function register_chatwp_widget()
{
    register_widget('ChatWP_Widget');
}
add_action('widgets_init', 'register_chatwp_widget');

// Add settings page for API key
function chatwp_api_key_init()
{
    register_setting('chatwp', 'chatwp_api_key');
}
add_action('admin_init', 'chatwp_api_key_init');

function chatwp_options_page()
{
    ?>
    <div class="wrap">
        <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
        <form action="options.php" method="post">
            <?php settings_fields('chatwp'); ?>
            <?php do_settings_sections('chatwp'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('API Key'); ?></th>
                    <td><input type="text" name="chatwp_api_key" value="<?php echo esc_attr(get_option('chatwp_api_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function chatwp_add_options_page()
{
    add_options_page(
        'ChatWP',
        'ChatWP',
        'manage_options',
        'chatwp',
        'chatwp_options_page'
    );
}
add_action('admin_menu', 'chatwp_add_options_page');

// Get completion text from OpenAI's Completions API
function chatwp_get_completion($api_key, $prompt, $model, $temperature, $max_tokens, $frequency_penalty, $presence_penalty)
{
    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    );
    $body = array(
        'prompt' => $prompt,
        'model' => $model,
        'temperature' => intval($temperature),
        'max_tokens' => intval($max_tokens),
        'frequency_penalty' => intval($frequency_penalty),
        'presence_penalty' => intval($presence_penalty)
    );
    $url = 'https://api.openai.com/v1/completions';
    $response = wp_remote_post($url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($body),
        'timeout' => 60,
        'httpversion' => '1.1',
        'user-agent' => 'ChatWP/1.0',
        'blocking' => true,
        'data_format' => 'body',
        'sslverify' => true,
        'query' => array(
            'api_key' => $api_key,
        ),
    ));


    if (is_wp_error($response)) {
        return false;
    }
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body);
    return $response_data;
}

