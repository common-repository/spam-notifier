<?php
/*
Plugin Name: Spam Notifier
Plugin URI: https://wordpress.org/plugins/spam-notifier/
Description: The plugin sends an email message when a comment goes to the spam folder.
Version: 2.00
Author: Flector
Author URI: https://profiles.wordpress.org/flector#content-plugins
Text Domain: spam-notifier
*/ 

//проверка версии плагина (запуск функции установки новых опций) begin
function sn_check_version() {
    $sn_options = get_option('sn_options');
    if (!isset($sn_options['version'])) {$sn_options['version']='';update_option('sn_options',$sn_options);}
    if ( $sn_options['version'] != '2.00' ) {
        sn_set_new_options();
    }    
}
add_action('plugins_loaded', 'sn_check_version');
//проверка версии плагина (запуск функции установки новых опций) end  

//функция установки новых опций при обновлении плагина у пользователей begin
function sn_set_new_options() { 
    $sn_options = get_option('sn_options');

    //если нет опции при обновлении плагина - записываем ее
    //if (!isset($sn_options['new_option'])) {$sn_options['new_option']='value';}
    
    //если необходимо переписать уже записанную опцию при обновлении плагина
    //$sn_options['old_option'] = 'new_value';
    
    //перенос старых настроек в новый формат begin
    $opt_comments = get_option('sn_opt_comments');
    $opt_trackbacks = get_option('sn_opt_trackbacks');
    
    if ( !isset($sn_options['comments']) ) {
        if ( $opt_comments == '1' ) {
            $sn_options['comments'] = 'enabled';
        } else {
            $sn_options['comments'] = 'disabled';
        }
    }
    if ( !isset($sn_options['trackbacks']) ) {
        if ( $opt_trackbacks == '1' ) {
            $sn_options['trackbacks'] = 'enabled';
        } else {
            $sn_options['trackbacks'] = 'disabled';
        }
    }
    
    delete_option('sn_opt_comments');
	delete_option('sn_opt_trackbacks');
    //перенос старых настроек в новый формат end
    
    $sn_options['version'] = '2.00';
    update_option('sn_options', $sn_options);
}
//функция установки новых опций при обновлении плагина у пользователей end

//функция установки значений по умолчанию при активации плагина begin
function sn_init() {

    $sn_options = array();

    $sn_options['version'] = '2.00';
    $sn_options['comments'] = 'enabled';
    $sn_options['trackbacks'] = 'enabled';
   
    add_option('sn_options', $sn_options);
}
add_action('activate_spam-notifier/spam-notifier.php', 'sn_init');
//функция установки значений по умолчанию при активации плагина end

//функция при деактивации плагина begin
function sn_on_deactivation() {
	if ( ! current_user_can('activate_plugins') ) return;
}
register_deactivation_hook( __FILE__, 'sn_on_deactivation' );
//функция при деактивации плагина end

//функция при удалении плагина begin
function sn_on_uninstall() {
	if ( ! current_user_can('activate_plugins') ) return;
    delete_option('sn_options');
}
register_uninstall_hook( __FILE__, 'sn_on_uninstall' );
//функция при удалении плагина end

//загрузка файла локализации плагина begin
function sn_setup(){
    load_plugin_textdomain('spam-notifier');
}
add_action('init', 'sn_setup');
//загрузка файла локализации плагина end

//добавление ссылки "Настройки" на странице со списком плагинов begin
function sn_actions($links) {
	return array_merge(array('settings' => '<a href="options-general.php?page=spam-notifier.php">' . __('Settings', 'spam-notifier') . '</a>'), $links);
}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ),'sn_actions');
//добавление ссылки "Настройки" на странице со списком плагинов end

//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина begin
function sn_files_admin($hook_suffix) {
	$purl = plugins_url('', __FILE__);
    if ( $hook_suffix == 'settings_page_spam-notifier' ) {
        if(!wp_script_is('jquery')) {wp_enqueue_script('jquery');}    
        wp_register_script('sn-lettering', $purl . '/inc/jquery.lettering.js');  
        wp_enqueue_script('sn-lettering');
        wp_register_script('sn-textillate', $purl . '/inc/jquery.textillate.js');
        wp_enqueue_script('sn-textillate');
        wp_register_style('sn-animate', $purl . '/inc/animate.min.css');
        wp_enqueue_style('sn-animate');
        wp_register_script('sn-script', $purl . '/inc/sn-script.js', array(), '2.00');  
        wp_enqueue_script('sn-script');
        wp_register_style('sn-css', $purl . '/inc/sn-css.css', array(), '2.00');
        wp_enqueue_style('sn-css');
    }
}
add_action('admin_enqueue_scripts', 'sn_files_admin');
//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина end

//функция вывода страницы настроек плагина begin
function sn_options_page() {
$purl = plugins_url('', __FILE__);

if (isset($_POST['submit'])) {
     
//проверка безопасности при сохранении настроек плагина begin       
if ( ! wp_verify_nonce( $_POST['sn_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?', 'spam-notifier' ));
}
//проверка безопасности при сохранении настроек плагина end
        
    //проверяем и сохраняем введенные пользователем данные begin    
    $sn_options = get_option('sn_options');
    
    if(isset($_POST['comments'])){$sn_options['comments'] = sanitize_text_field($_POST['comments']);}else{$sn_options['comments'] = 'disabled';}
    if(isset($_POST['trackbacks'])){$sn_options['trackbacks'] = sanitize_text_field($_POST['trackbacks']);}else{$sn_options['trackbacks'] = 'disabled';}
    
    update_option('sn_options', $sn_options);
    //проверяем и сохраняем введенные пользователем данные end
}
$sn_options = get_option('sn_options');
?>
<?php   if (!empty($_POST) ) :
if ( ! wp_verify_nonce( $_POST['sn_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?', 'spam-notifier' ));
}
?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'spam-notifier'); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('&#171;Spam Notifier&#187; Settings', 'spam-notifier'); ?></h2>

<div class="metabox-holder" id="poststuff">
<div class="meta-box-sortables">

<?php $lang = get_locale(); ?>
<?php if ($lang == 'ru_RU') { ?>
<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode">Вам нравится этот плагин ?</span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="Купить мне чашку кофе :)" style=" margin: 5px; float:left;" />
        <p>Привет, меня зовут <strong>Flector</strong>.</p>
        <p>Я потратил много времени на разработку этого плагина.<br />
		Поэтому не откажусь от небольшого пожертвования :)</p>
        <a target="_blank" id="yadonate" href="https://money.yandex.ru/to/41001443750704/200">Подарить</a> 
        <p>Или вы можете заказать у меня услуги по WordPress, от мелких правок до создания полноценного сайта.<br />
        Быстро, качественно и дешево. Прайс-лист смотрите по адресу <a target="new" href="https://www.wpuslugi.ru/?from=sn-plugin">https://www.wpuslugi.ru/</a>.</p>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } else { ?>
<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Do you like this plugin ?', 'spam-notifier'); ?></span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="<?php _e('buy me a coffee', 'spam-notifier'); ?>" style=" margin: 5px; float:left;" />
        <p><?php _e('Hi! I\'m <strong>Flector</strong>, developer of this plugin.', 'spam-notifier'); ?></p>
        <p><?php _e('I\'ve been spending many hours to develop this plugin.', 'spam-notifier'); ?> <br />
		<?php _e('If you like and use this plugin, you can <strong>buy me a cup of coffee</strong>.', 'spam-notifier'); ?></p>
        <a target="new" href="https://www.paypal.me/flector"><img alt="" src="<?php echo $purl . '/img/donate.gif'; ?>" title="<?php _e('Donate with PayPal', 'spam-notifier'); ?>" /></a>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } ?>

<form action="" method="post">

<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Options', 'spam-notifier'); ?></span></h3>
    <div class="inside" style="display: block;">

        <table class="form-table">
        
            <tr>
                <th class="tdcheckbox"><?php _e('Comments:', 'spam-notifier'); ?></th>
                <td>
                    <label for="comments"><input type="checkbox" value="enabled" name="comments" id="comments" <?php if ($sn_options['comments'] == 'enabled') echo 'checked="checked"'; ?> /><?php _e('Send an email if a regular comment was marked as spam.', 'spam-notifier'); ?></label>
                </td>
            </tr>
            <tr>
                <th class="tdcheckbox"><?php _e('Pingbacks and Trackbacks:', 'spam-notifier'); ?></th>
                <td>
                    <label for="trackbacks"><input type="checkbox" value="enabled" name="trackbacks" id="trackbacks" <?php if ($sn_options['trackbacks'] == 'enabled') echo 'checked="checked"'; ?> /><?php _e('Send an email if a comment containing pingback or trackback was marked as spam.', 'spam-notifier'); ?></label>
                </td>
            </tr>

            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Update options &raquo;', 'spam-notifier'); ?>" />
                </td>
            </tr> 
        </table>
    </div>
</div>

<div class="postbox" style="margin-bottom:0;">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('About', 'spam-notifier'); ?></span></h3>
	  <div class="inside" style="padding-bottom:15px;display: block;">
     
      <p><?php _e('If you liked my plugin, please <a target="new" href="https://wordpress.org/plugins/spam-notifier/"><strong>rate</strong></a> it.', 'spam-notifier'); ?></p>
      <p style="margin-top:20px;margin-bottom:10px;"><?php _e('You may also like my other plugins:', 'spam-notifier'); ?></p>
      
      <div class="about">
        <ul>
            <?php if ($lang == 'ru_RU') : ?>
            <li><a target="new" href="https://ru.wordpress.org/plugins/rss-for-yandex-zen/">RSS for Yandex Zen</a> - создание RSS-ленты для сервиса Яндекс.Дзен.</li>
            <li><a target="new" href="https://ru.wordpress.org/plugins/rss-for-yandex-turbo/">RSS for Yandex Turbo</a> - создание RSS-ленты для сервиса Яндекс.Турбо.</li>
            <?php endif; ?>
            <li><a target="new" href="https://wordpress.org/plugins/bbspoiler/">BBSpoiler</a> - <?php _e('this plugin allows you to hide text under the tags [spoiler]your text[/spoiler].', 'spam-notifier'); ?></li>
            <li><a target="new" href="https://wordpress.org/plugins/easy-textillate/">Easy Textillate</a> - <?php _e('very beautiful text animations (shortcodes in posts and widgets or PHP code in theme files).', 'spam-notifier'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/cool-image-share/">Cool Image Share</a> - <?php _e('this plugin adds social sharing icons to each image in your posts.', 'spam-notifier'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/today-yesterday-dates/">Today-Yesterday Dates</a> - <?php _e('this plugin changes the creation dates of posts to relative dates.', 'spam-notifier'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/truncate-comments/">Truncate Comments</a> - <?php _e('this plugin uses Javascript to hide long comments (Amazon-style comments).', 'spam-notifier'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/easy-yandex-share/">Easy Yandex Share</a> - <?php _e('share buttons for WordPress from Yandex. ', 'spam-notifier'); ?> </li>
            </ul>
      </div>     
    </div>
</div>
<?php wp_nonce_field( plugin_basename(__FILE__), 'sn_nonce'); ?>
</form>
</div>
</div>
<?php 
}
//функция вывода страницы настроек плагина end

//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" begin
function sn_menu() {
	add_options_page('Spam Notifier', 'Spam Notifier', 'manage_options', 'spam-notifier.php', 'sn_options_page');
}
add_action('admin_menu', 'sn_menu');
//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" end

//функция отсылки уведомления при попадании комментария в спам begin
function sn_notify_spam($comment_id) {
	global $wpdb;
	
	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID='$comment_id' LIMIT 1");
	$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID='$comment->comment_post_ID' LIMIT 1");
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $comment_content = wp_specialchars_decode( $comment->comment_content );
	$author  = get_userdata( $post->post_author );	
    $comment_author_domain = @gethostbyaddr($comment->comment_author_IP);

	$sn_options = get_option('sn_options');
	if (($sn_options['comments'] == 'disabled') AND (get_comment_type($comment) == 'comment' ))  {return;}
	if (($sn_options['trackbacks'] == 'disabled') AND (get_comment_type($comment) != 'comment' )) {return;}
	
	if ($comment->comment_approved == 'spam') {

		$notify_message  = sprintf( __('Spam comment on: "%s"', 'spam-notifier' ), $post->post_title ) . "\r\n\r\n";
        $notify_message .= sprintf( __('Author : %1$s (IP: %2$s, %3$s)', 'spam-notifier'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
        $notify_message .= sprintf( __('Email  : %s', 'spam-notifier'), $comment->comment_author_email ) . "\r\n";
        if ( $comment->comment_author_url ) {
            $notify_message .= sprintf( __('URL    : %s', 'spam-notifier'), $comment->comment_author_url ) . "\r\n";
        }    
		$notify_message .= sprintf( __('Whois : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s', 'spam-notifier'), $comment->comment_author_IP ) . "\r\n\r\n";
        $notify_message .= sprintf( __('Comment: %s', 'spam-notifier' ), "\r\n" . $comment_content ) . "\r\n\r\n";
		$notify_message .= __('You can see all comments on this post here:', 'spam-notifier' ) . "\r\n";
        $subject = sprintf( __('[%1$s] Comment: "%2$s"', 'spam-notifier'), $blogname, $post->post_title );
        $notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
        
        $notify_message .= sprintf( __('Delete it: %s', 'spam-notifier'), admin_url( "comment.php?action=delete&c={$comment->comment_ID}#wpbody-content" ) ) . "\r\n";
        $notify_message .= sprintf( __('Approve it: %s', 'spam-notifier'), admin_url( "comment.php?action=approve&c={$comment->comment_ID}#wpbody-content" ) ) . "\r\n";
        
        $subject = "[Spam Notifier] " . $subject;
        $message_headers = "$from\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
        
        $notify_message = apply_filters('comment_notification_text', $notify_message, $comment->comment_ID);
        $subject = apply_filters('comment_notification_subject', $subject, $comment->comment_ID);
		$message_headers = apply_filters('comment_notification_headers', $message_headers, $comment->comment_ID);
	
		@wp_mail($author->user_email, wp_specialchars_decode( $subject ), $notify_message, $message_headers);
	}
}
add_action('comment_post', 'sn_notify_spam');
//функция отсылки уведомления при попадании комментария в спам end