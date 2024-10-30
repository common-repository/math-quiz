<?php
/*
Plugin Name: Math Quiz
Plugin URI: http://wordpress.org/extend/plugins/math-quiz/
Description: Generating random math problem for comment form.
Text Domain: math-quiz
Domain Path: /languages
Version: 2.0.0
Author: ATI
Author URI: https://atifans.net/
License: GPL2 or later
*/

//Define constants
define('SETTING_VERSION', '4.0');

//Make sure the plugin is not called outside WP
if ( !function_exists( 'add_action' ) ) {
  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
  exit;
}

//Include admin functions
if ( is_admin() )
  require_once dirname( __FILE__ ) . '/admin.php';

//Include secure random lib
if ( !function_exists( 'random_int' ) )
  require_once dirname( __FILE__ ) . '/lib/random_compat.phar';

//*******************************//
//*****Initialize the plugin*****//
//*******************************//
function start_math_engine(){
  
  //Register translation
  load_plugin_textdomain('math-quiz', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
  
  //Read plugin setting
  $quiz_setting = get_option('math-quiz-setting');
  if ( empty( $quiz_setting['setting_version'] ) || $quiz_setting['setting_version'] != SETTING_VERSION )
    update_setting();
  
  //Ajax hook
  if ( isset($_GET['math_quiz_ajax']) && $_GET['math_quiz_ajax'] == 'get_problem' ) {
    get_math_problem();
    exit();
  }

  //Prepare plugin hooks
  if ( !is_admin() ) {
    // enqueue jQuery lib
    wp_enqueue_script('jquery');
    // echo customized style sheet
    if( $quiz_setting['quiz-css'] == 'plugin' )
      add_action( 'wp_head', 'get_style_sheet' );
    // form hook
    add_action('comment_form', function(){ if(!current_user_can('publish_posts')) get_ajax_script(); });
    // comment-process hook
    add_filter('preprocess_comment', 'comment_math_hook');

    if ( isset($quiz_setting['login-check']) && $quiz_setting['login-check'] ) {
      add_action('login_enqueue_scripts', function(){ wp_enqueue_script('jquery'); });
      add_action('login_footer', 'get_ajax_script');
      add_action('authenticate', 'login_math_hook', 21, 1);
    }
  }
}
add_action('init', 'start_math_engine');

//***************************************//
//*****Background handling functions*****//
//***************************************//

//Random number generator
function number_engine($quiz_type = 'pic'){
  $uniqueid = base64_encode(random_bytes(45));
  
  //Randomly + or - 
  $num1 = random_int(10, 50);
  $num2 = random_int(1, $num1-1);
  if( random_int(0, 1) ){
    $problem = $num1 . ' + ' . $num2 . ' = ?';
    $answer = $num1 + $num2;
  }else{
    $problem = $num1 . ' - ' . $num2 . ' = ?';
    $answer = $num1 - $num2;
  }
  
  if ($quiz_type == 'pic') {
    $problem = picture_generator( $problem );
  }
  
  return array($problem, $answer, $uniqueid);
}

//Update current database data or initialize the setting
function update_setting(){
  $init_setting = array(
    'quiz-css' => 'theme',
    'quiz-css-content' => '',
    'quiz-position-selector' => 'default',
    'quiz-position' => 'submit',
    'quiz-ajax' => 'after',
    'quiz-type' => 'pic',
    'login-check' => '0',
    'problem_store_name' => 'math_quiz_problems',
    'problem_store_version' => '1',
    'setting_version' => SETTING_VERSION
  );
  
  $quiz_setting = get_option('math-quiz-setting');
  
  //If there's a existing setting, merge it and remove old one.
  if( !empty($quiz_setting['setting_version']) && version_compare( $quiz_setting['setting_version'], SETTING_VERSION, '<' )){
    $intersect = array_intersect($init_setting, $quiz_setting);
    $quiz_setting = array_merge( $init_setting, $intersect );
    $quiz_setting['setting_version'] = SETTING_VERSION;
    update_option( 'math-quiz-setting', $quiz_setting );
  }else{
    add_option( 'math-quiz-setting', $init_setting );
  }

  create_table();
}

function create_table(){
  global $wpdb;

  $quiz_setting = get_option('math-quiz-setting');

  $table_name = $wpdb->prefix . $quiz_setting['problem_store_name'];
  $charset_collate = $wpdb->get_charset_collate();

  if ( $wpdb->get_var("SELECT 1 FROM $table_name LIMIT 1") ) {
    return;
  }

  $sql = "CREATE TABLE $table_name (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    unique_id VARCHAR(255) NOT NULL,
    answer TINYINT NOT NULL,
    created_at INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    INDEX answer_index (unique_id, answer),
    INDEX stale_problem_index (created_at)
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

//Fixed quiz form
function get_quiz_form($quiz_type = 'pic'){
  $output = '<p id="mathquiz"><label for="mathquiz">%problemlabel%';
  $output .= ($quiz_type == 'pic') ? '<img src="data:image/jpeg;base64,%problem%">' : '%problem%';
  $output .= '</label>&nbsp;<input name="math-quiz" type="text" />&nbsp;<a id="refresh-mathquiz" href="javascript:void(0)">%reloadbutton%</a><input type="hidden" name="uniqueid" value="%uniqueid%" /></p>';

  return $output;
}

//Base64 picture generator
function picture_generator( $text ){
   // constant values
  $backgroundSizeX = 2000;
  $backgroundSizeY = 350;
  $sizeX = 120;
  $sizeY = 30;
  $fontFile = dirname( __FILE__ ) . '/lib/SourceCodePro-Bold.ttf';
  $textLength = strlen($text);
 
  // generate random security values
  $backgroundOffsetX = random_int(0, $backgroundSizeX - $sizeX - 1);
  $backgroundOffsetY = random_int(0, $backgroundSizeY - $sizeY - 1);
  $angle = random_int(-5, 5);
  $fontColorR = random_int(50, 60);
  $fontColorG = random_int(50, 60);
  $fontColorB = random_int(50, 60);
  $fontSize = random_int(14, 16);
  $textX = random_int(0, (int)($sizeX - 0.68 * $textLength * $fontSize)); // these coefficients are empiric
  $textY = random_int((int)($fontSize * 1.2), (int)($sizeY - 0.5 * $fontSize)); // don't try to learn how they were taken out

  // create image with background
  $src_im = imagecreatefrompng( dirname( __FILE__ ) . "/lib/background.png");
  $dst_im = imagecreatetruecolor($sizeX, $sizeY);
  $resizeResult = imagecopyresampled($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);

  $color = imagecolorallocate($dst_im, $fontColorR, $fontColorG, $fontColorB);

  imagettftext($dst_im, $fontSize, -$angle, $textX, $textY, $color, $fontFile, $text);

  ob_start();
    imagejpeg($dst_im, NULL, 80);
    $imagedata = ob_get_contents(); // read from buffer
    imagedestroy($src_im); // free memory
    imagedestroy($dst_im);
  ob_end_clean(); // delete buffer
  
  return base64_encode($imagedata);
}

function check_DNS_validity($host, $ip){
  $dnsV4 = dns_get_record( $host, DNS_A );
  $dnsV6 = dns_get_record( $host, DNS_AAAA );

  foreach ($dnsV4 as $record) {
    if( $record['ip'] == $ip )
      return true;
  }

  foreach ($dnsV6 as $record) {
    if( $record['ip'] == $ip )
      return true;
  }

  return false;
}

//***********************************//
//*****Action handling functions*****//
//***********************************//

//Generate math problem for unknown users
function get_math_problem(){
  global $wpdb;

  //Support cross domain AJAX call
  header('Access-Control-Allow-Origin: ' . home_url() );

  //Set content type
  header("Content-Type: text/html; charset=UTF-8");

  //Get things from the number engine
  $quiz_setting = get_option('math-quiz-setting');
  list( $problem, $answer, $uniqueid ) = number_engine( $quiz_setting['quiz-type'] );

  //Store them into database
  $table_name = $wpdb->prefix . $quiz_setting['problem_store_name'];
  $wpdb->insert(
    $table_name,
    array(
      'unique_id' => $uniqueid,
      'answer' => $answer,
      'created_at' => time()
    )
  );

  //Filter specific string
  $stringToBeReplace = array(
    '%problem%',
    '%uniqueid%',
    '%problemlabel%',
    '%reloadbutton%'
  );
  $stringToReplace = array(
    $problem,
    htmlentities($uniqueid),
    __('Solve the problem: ', 'math-quiz'),
    __('Refresh Quiz', 'math-quiz')
  );
  $fireworks = str_replace( $stringToBeReplace, $stringToReplace, get_quiz_form($quiz_setting['quiz-type']) );

  echo $fireworks;
}

//Echo ajax code
function get_ajax_script(){
  //Get quiz setting
  $quiz_setting = get_option('math-quiz-setting');
  
  //Check setting
  if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
    $selector = '$(".forgetmenot").before(response);';
  }elseif( $quiz_setting['quiz-position-selector'] == 'custom'){
    $selector = '$(' . json_encode('#'.$quiz_setting['quiz-position']) . ').' . json_encode($quiz_setting['quiz-ajax']) . '(response);';
  }else{
    $selector = '$("#submit").parent().before(response);';
  }

  $ajax_code = 
'<script type="text/javascript">
  (function($){
  var MathQuizCall = function(){
      $.ajax({
        type : "GET",
        url : "'. site_url() .'/index.php",
        data : { math_quiz_ajax : "get_problem" },
        success : function(response){'. $selector .'}
      });
    };
  var MathQuizRefresh = function(){
      $.ajax({
        type : "GET",
        url : "'. site_url() .'/index.php",
        data : { math_quiz_ajax : "get_problem" },
        success : function(response){
          $("#mathquiz").replaceWith(response);
        }
      });
    };
    
  jQuery(document).ready(function() {
    MathQuizCall();
    $("body").on("click", "#refresh-mathquiz", MathQuizRefresh);
    $("form").on("submit", MathQuizRefresh) 
  });
  })(jQuery);
</script>';
  
  echo $ajax_code;
  return true;
}

//Echo style sheet
function get_style_sheet(){
  //Get quiz setting
  $quiz_setting = get_option('math-quiz-setting');
  
  $style = '<style type="text/css">' . htmlspecialchars($quiz_setting['quiz-css-content'], ENT_NOQUOTES) . '</style>';
  
  echo $style;
  return true;
}

//comment process hook
function comment_math_hook( $commentdata ){
  //Split post data
  extract( $commentdata );
  
  //Check user identity and comment type
  if( !current_user_can( 'publish_posts' ) &&
    $comment_type != 'pingback' &&
    $comment_type != 'trackback' ) {

    $error = check_math_answer();
    if ( !is_null($error) ) {
      wp_die( $error );
    }

  } else if ( $comment_type == 'trackback' ||
        $comment_type == 'pingback' ) { //Check trackback and pingback spams

    $parsedUrl = parse_url( $_POST['url'] );

    if( !check_DNS_validity( $parsedUrl['host'], $_SERVER['REMOTE_ADDR'] ) ) {
      wp_die( __( 'Source IP and url are not matched.', 'math-quiz' ) );
    }
  }
  
  return $commentdata;
}

//login form hook
function login_math_hook( $user ){
  if ( isset($_POST['wp-submit']) ) {
    $error = check_math_answer();

    if( !is_null($error) )
      return new WP_Error( 'mathquiz_error', $error );
  }

  return $user;
}

//Check answer
function check_math_answer(){
  global $wpdb;

  $quiz_setting = get_option('math-quiz-setting');
  $table_name = $wpdb->prefix . $quiz_setting['problem_store_name'];

  //Throw if parameters are missing
  if ( empty( $_POST['uniqueid'] ) || empty( $_POST['math-quiz'] ) ) {
    return __( 'You forgot to fill out math problem.', 'math-quiz' );
  }

  //Retrieve answer
  $answer = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT answer FROM $table_name WHERE unique_id = %s",
      $_POST['uniqueid']
    )
  );

  //Attempt made, destroy the uniqueid and clean up old problems
  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM $table_name WHERE unique_id = %s OR created_at < %d",
      $_POST['uniqueid'],
      time() - (60 * 60 * 24 * 7)
    )
  );

  //Check answer
  if ( is_null($answer) ) {
    return __( 'Invalid problem. Please go back and try another one.', 'math-quiz' );
  } elseif ( $_POST['math-quiz'] != $answer ) {
    return __( 'The answer is incorrect.  Please go back and try another problem.', 'math-quiz' );
  }
}
