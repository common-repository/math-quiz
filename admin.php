<?php

//Make sure the plugin is not called outside WP
if ( !function_exists( 'add_action' ) ) {
  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
  exit;
}

add_action( 'admin_menu', 'math_quiz_menu' );

function math_quiz_menu(){
  add_options_page('Math Quiz', 'Math Quiz', 'manage_options', 'math-quiz-menu', 'math_setting_page');
}

//Return setting content
function math_setting_page(){

  if ( !empty($_POST['submit'] ) )
    $save_result = save_setting();

  //Get quiz setting
  $quiz_setting = get_option('math-quiz-setting');
  
  //Enqueue jQuery
  wp_enqueue_script('jquery');
?>
<div class="wrap">
  <h2><?php _e('Math Quiz', 'math-quiz'); ?></h2>
  <?php 
    if( !empty($_POST['submit'] ) ) { 
  ?>
    <div id="setting-error-settings_updated" class="updated settings-error"> 
    <p><strong>
    <?php 
      if( strlen($save_result) > 0 ) {
        _e('The following settings is invalid, please try again. <br />', 'math-quiz');
        echo $save_result;
      }else{
        _e('Setting saved.', 'math-quiz');
      }
    ?>
    </strong></p></div>
  <?php 
    } //Notice box ended here
  ?>
  <form name="math-quiz-form" method="post" action="">
  <?php wp_nonce_field( 'math-quiz-control-panel' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row"><label for="quiz-form"><?php _e('Quiz Form', 'math-quiz'); ?></label></th>
        <td>
        <textarea id="quiz-form" class="code disabled" disabled="disabled" cols="80" rows="5"><?php
          echo htmlspecialchars(get_quiz_form($quiz_setting['quiz-type']));
         ?></textarea>
        <p class="description"><?php _e('This is how your quiz form will look like, customize it using CSS!', 'math-quiz'); ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="quiz-css"><?php _e('Quiz Form Customization', 'math-quiz'); ?></label></th>
        <td>
        <select name="quiz-css" id="quiz-css">
          <?php
            enumerate_options('login-check', array(
              'theme' => __('Use theme CSS', 'math-quiz'),
              'plugin' => __('Customize it here', 'math-quiz')
            ));
          ?>
        </select><br><br>
        <textarea name="quiz-css-content" id="plugin" class="quiz-css-content code" cols="40" rows="10" <?php
          echo ($quiz_setting['quiz-css'] != 'plugin') ? 'style="display:none">' : '>';
          echo htmlspecialchars($quiz_setting['quiz-css-content']);
         ?></textarea>
        <p class="description"><?php _e('It\'s recommended to use theme CSS.', 'math-quiz'); ?></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="quiz-position"><?php _e('Quiz Position', 'math-quiz'); ?></label></th>
        <td>
        <select name="quiz-position-selector" id="quiz-position-selector">
          <?php
            enumerate_options('login-check', array(
              'default' => __('Use default position', 'math-quiz'),
              'custom' => __('Customize it yourself', 'math-quiz')
            ));
          ?>
        </select><br><br>
        <div id="custom" class="custom-position" <?php if($quiz_setting['quiz-position-selector'] != 'custom') echo 'style="display:none"'; ?>>
        <select name="quiz-ajax" id="quiz-ajax">
          <?php
            enumerate_options('login-check', array(
              'before' => __('Insert before', 'math-quiz'),
              'html' => __('Insert into', 'math-quiz'),
              'after' => __('Insert after', 'math-quiz')
            ));
          ?>
        </select>
        <?php _e('the HTML element id: ', 'math-quiz'); ?>
        <input name="quiz-position" type="text" id="quiz-position" value="<?php echo htmlspecialchars($quiz_setting['quiz-position']); ?>" class="regular-text" placeholder="HTML element id here"/>
        <p class="description"><?php _e('Please enter the "HTML element id" where you want to insert the quiz form, and choose a insert method.', 'math-quiz'); ?></p>
        </div>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="quiz-type"><?php _e('Quiz Type', 'math-quiz'); ?></label></th>
        <td>
        <select name="quiz-type" id="quiz-type">
          <?php
            enumerate_options('login-check', array(
              'pic' => __('Picture', 'math-quiz'),
              'test' => __('Text', 'math-quiz')
            ));
          ?>
        </select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="login-check"><?php _e('Login captcha', 'math-quiz'); ?></label></th>
        <td>
        <select name="login-check" id="login-check">
          <?php
            enumerate_options('login-check', array(
              '0' => __('Off', 'math-quiz'),
              '1' => __('On', 'math-quiz')
            ));
          ?>
        </select>
        </td>
      </tr>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'math-quiz'); ?>"  /></p>
  </form>
</div>
<script type="text/javascript">
(function($) {
    $('#quiz-css').change(function(){
        $('.quiz-css-content').hide();
        $('#' + $(this).val()).show();
    });
  $('#quiz-position-selector').change(function(){
        $('.custom-position').hide();
        $('#' + $(this).val()).show();
    });
})(jQuery);
</script>
<?php
} //math_setting_page ends here

function save_setting(){
  if ( !check_admin_referer( 'math-quiz-control-panel' ) ) {
    return;
  }

  //Get quiz setting
  $quiz_setting = get_option('math-quiz-setting');
      
  //Check quiz-css
  if( $_POST['quiz-css'] == 'theme' ){
    $quiz_setting['quiz-css'] = 'theme';
  }else if( $_POST['quiz-css'] == 'plugin' ){
    $quiz_setting['quiz-css'] = 'plugin';
    $quiz_setting['quiz-css-content'] = $_POST['quiz-css-content'];
  }else{
    set_error('Quiz Customization');
  }
    
  //Check quiz-position-selector
  if( $_POST['quiz-position-selector'] == 'default' || $_POST['quiz-position-selector'] == 'custom' ){
    $quiz_setting['quiz-position-selector'] = $_POST['quiz-position-selector'];
  }else{
    set_error('Quiz Position Type Selector');
  }
    
  //Check quiz-position
  if( preg_match("/^[^ ]+$/", $_POST['quiz-position']) ){
    $quiz_setting['quiz-position'] = $_POST['quiz-position'];
  }else{
    set_error('Quiz Position');
  }
    
  //Check quiz-ajax
  if( $_POST['quiz-ajax'] == 'before' || $_POST['quiz-ajax'] == 'html' || $_POST['quiz-ajax'] == 'after' ){
    $quiz_setting['quiz-ajax'] = $_POST['quiz-ajax'];
  }else{
    set_error('Quiz Insert Method');
  }

  //Check quiz-type
  if( $_POST['quiz-type'] == 'pic' || $_POST['quiz-type'] == 'test' ){
    $quiz_setting['quiz-type'] = $_POST['quiz-type'];
  }else{
    set_error('Quiz Type');
  }

  if( $_POST['login-check'] == '0' || $_POST['login-check'] == '1' ){
    $quiz_setting['login-check'] = $_POST['login-check'];
  }else{
    set_error('Login captcha');
  }
  
  update_option( 'math-quiz-setting', stripslashes_deep($quiz_setting) );
  
  return set_error();
}

function set_error($type = null)
{
  static $setting_error = '';

  if ($type == null) {
    return $setting_error;
  }

  if(strlen($setting_error) > 0)
    $setting_error .= ', ';
  $setting_error .= __($type, 'math-quiz');
}

function enumerate_options($setting_name, $options){
  $quiz_setting = get_option('math-quiz-setting');
  
  foreach ($options as $key => $value) {
    echo '<option value="'. htmlspecialchars($key) .'"';
    if( $key == $quiz_setting[$setting_name] ) echo ' selected="selected"';
    echo '>'. htmlspecialchars($value) .'</option>';
  }
}
