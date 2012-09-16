<?php
/*
Plugin Name: SO Audible Cloud Music Player
Plugin URI: http://www.soaudible.com/
Description: So Audible Cloud Music Player, sync your tracks share with friends upload, quick share embed, html5, ipad, iphone, android, play music anywhere long mixes, meetings, sermons, podcasts, radio. 
Version: 0.9
Author: Samuel East
Author URI: http://www.soaudible.com/
License: GPL2
*/ 
 
/*  Copyright YEAR  Samuel East  (email : mail@samueleast.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 


if (!class_exists("soaudible")) {
	class soaudible {
		
		
		// property declaration
        public $soaudible_username = '';
		public $soaudible_email = '';
		public $bucket          = '';
		public $folder          = '';
		public $colour          = '#000';
		public $width           = '400px';
		public $bgimage         = '';
		public $radius          = '15px';
		public $autoplay        = 'yes';
		public $jtoggle		    = 'false';
		//public $limit          = '100';
		
	    function soaudible() { //constructor
		
			$this->__construct();

		}
		
		function __construct(){
			
			// Set Plugin Path  
			$this->pluginPath = dirname(__FILE__);  
	  
			// Set Plugin URL  
			$this->pluginUrl = WP_PLUGIN_URL . '/so-audible';
			
			// Put our defaults in the "wp-options" table
			add_option("s3-soaudible_username", $this->soaudible_username);
			add_option("s3-soaudible_email", $this->soaudible_email);
			add_option("s3-bucket", $this->bucket);
			add_option("s3-folder", $this->folder); 
			add_option("s3-colour", $this->colour);
			add_option("s3-width", $this->width);
			add_option("s3-bgimage", $this->bgimage);
			add_option("s3-radius", $this->radius);
			add_option("s3-autoplay", $this->autoplay);
			add_option("s3-jtoggle", $this->jtoggle);
			//add_option("s3-limit", $this->limit);
			
			add_action('admin_menu', array( $this, 'soaudible_admin_menu' ));
			add_action( 'wp_head', array( $this, 'soaudible_css' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'include_jquery' ) );
			add_action( 'wp_footer', array( $this, 'soaudible_javascript' ) );
			add_action( 'admin_head', array( $this, 'soaudible_css_admin' ) );
			add_action( 'admin_footer', array( $this, 'soaudible_javascript_admin' ) );
			add_shortcode( 'soaudible', array( $this, 'soaudible_player' ) );
			
			
		} // function
		
		
		function include_jquery(){
			
			   wp_deregister_script('jquery');
			   wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"), false, '1.7.1');
			   wp_enqueue_script('jquery');
			
			
			
		}
		
		// include css
		function soaudible_css(){
			
			echo '<link rel="stylesheet" href="'.$this->pluginUrl.'/css/style.css" />';
			
		}
		
		// include css
		function soaudible_css_admin(){
			
			echo '<link rel="stylesheet" href="'.$this->pluginUrl.'/css/colorpicker.css" />';
			
		}
		
		// include javascript
		function soaudible_javascript(){
			
			$jtoggle = get_option("s3-jtoggle");	
			
			if($jtoggle == 'true'){
			echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>';
			}
			echo '<script type="text/javascript" src="'.$this->pluginUrl.'/js/soaudible.min.js"></script>';
			
		}
		
		// include javascript
		function soaudible_javascript_admin(){
			
			echo '<script type="text/javascript" src="'.$this->pluginUrl.'/js/colorpicker.js"></script>';
			echo '<script type="text/javascript" src="'.$this->pluginUrl.'/js/eye.js"></script>';
			echo '<script type="text/javascript" src="'.$this->pluginUrl.'/js/utils.js"></script>';
			echo '<script type="text/javascript" src="'.$this->pluginUrl.'/js/layout.js"></script>';
 
			?><script type="text/javascript">
			    (function($){
					$("#colour").ColorPicker({
						onSubmit: function (hsb, hex, rgb, el) {
							$(el).val(hex);
							$(el).ColorPickerHide();
						},
						onBeforeShow: function () {
							$("#colour").val(this.value);
							$(this).ColorPickerSetColor(this.value);
				
						},
						onChange: function (hsb, hex, rgb) {
							$("#colour").val("#" + hex);
							$(".jp-type-playlist").css("backgroundColor", "#" + hex);
						}
					}).bind("keyup", function () {
						$(this).ColorPickerSetColor(this.value);
					});
				
				
				$('.s3abgimage').click(function(){
	
					$('#bgimage').val($(this).attr('href'));
					
					return false;
					
				});
				
				
				
				})(jQuery);</script>

			<?php
			
		}
		
		function soaudible_admin_menu()  
    	{	
			$icon_url = "https://s3.amazonaws.com/s3audible/icons/wp_icon.png";
			add_menu_page( 'soaudible', 'soaudible', 10, 'soaudible', array($this, 'soaudible_admin'), $icon_url );
    	}
		
		function soaudible_admin()  
    	{	
			
			if ( isset($_POST['submit']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 'isd-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('isd-updatesettings');	
			// Get our new option values
			$soaudible_username	= $_POST['soaudible_username'];
			$soaudible_email	= $_POST['soaudible_email'];
			$bucket				= $_POST['bucket'];
			$folder				= $_POST['folder'];
			$colour				= addslashes($_POST['colour']);
			$width				= addslashes($_POST['width']);
			$bgimage			= addslashes($_POST['bgimage']);
			$radius				= addslashes($_POST['radius']);
			$autoplay			= addslashes($_POST['autoplay']);
			$jtoggle			= addslashes($_POST['jtoggle']);
			//$limit				= addslashes($_POST['limit']);
			
		    // Update the DB with the new option values
			update_option("s3-soaudible_username", mysql_real_escape_string($soaudible_username));
			update_option("s3-soaudible_email", mysql_real_escape_string($soaudible_email));
			update_option("s3-bucket", mysql_real_escape_string($bucket));
			update_option("s3-folder", mysql_real_escape_string($folder));
			update_option("s3-colour", mysql_real_escape_string($colour));
			update_option("s3-width", mysql_real_escape_string($width));
			update_option("s3-bgimage", mysql_real_escape_string($bgimage));
			update_option("s3-radius", mysql_real_escape_string($radius));
			update_option("s3-autoplay", mysql_real_escape_string($autoplay));
			update_option("s3-jtoggle", mysql_real_escape_string($jtoggle));
			//update_option("s3-limit", mysql_real_escape_string($limit));
			}
			
			$soaudible_username	= get_option("s3-soaudible_username");
			$soaudible_email	= get_option("s3-soaudible_email");
			$bucket	            = get_option("s3-bucket");
			$folder				= get_option("s3-folder");
			$colour				= get_option("s3-colour");
			$width				= get_option("s3-width");
			$bgimage			= get_option("s3-bgimage");
			$radius				= get_option("s3-radius");
			$autoplay			= get_option("s3-autoplay");
			$jtoggle	        = get_option("s3-jtoggle");	
			//$limit				= get_option("s3-limit");			
			
?>

<div class="wrap">

  <h1><img src="<?php echo $this->pluginUrl; ?>/images/s3audiblelogo.png" width="86" height="55" alt="soaudible"/> Soaudible Cloud Music Player</h1>  
  
  <h2>Please sign up for an account at soaudible you will need to use the email and username you signed up with <a href="http://soaudible.com/wp-login.php?action=register" target="_blank">http://soaudible.com/</a></h2>
  
  <form action="" method="post" id="isd-config">
    <table class="form-table">
      <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('isd-updatesettings'); } ?>
       <tr>
        <th scope="row" valign="top"><label for="soaudible_username">soaudible Username:</label></th>
        <td><input type="password" name="soaudible_username" id="soaudible_username" class="regular-text" value="<?php echo $soaudible_username; ?>"/></td>
      </tr> 
       <tr>
        <th scope="row" valign="top"><label for="soaudible_email">soaudible Email:</label></th>
        <td><input type="password" name="soaudible_email" id="soaudible_email" class="regular-text" value="<?php echo $soaudible_email; ?>"/></td>
      </tr> 
      <tr>
        <th scope="row" valign="top"><label for="colour">Background Color:</label></th>
        <td><input type="text" name="colour" id="colour" class="regular-text" value="<?php echo $colour; ?>"/></td>
      </tr>
      
       <tr>
        <th scope="row" valign="top"><label for="width">Player Width:</label></th>
        <td><input type="text"  name="width" id="width" class="regular-text" value="<?php echo $width; ?>"/></td>
      </tr>
      
     <tr>
        <th scope="row" valign="top"><label for="bgimage">Background Image Url:</label></th>
        <td><input type="text"  name="bgimage" id="bgimage" class="regular-text" value="<?php echo $bgimage; ?>"/>
        </td>
      </tr>
       
       <tr>
       <th scope="row" valign="top"></th>
        <td><a style="float:left; margin-right:10px;" class="s3abgimage" href="<?php echo $this->pluginUrl; ?>/images/128-62.jpg"><img src="<?php echo $this->pluginUrl; ?>/images/128-62.jpg" width="40" height="40"/></a><a style="float:left; margin-right:10px;" class="s3abgimage" href="<?php echo $this->pluginUrl; ?>/images/128-169.jpg"><img src="<?php echo $this->pluginUrl; ?>/images/128-169.jpg" width="40" height="40"/></a><a style="float:left; margin-right:10px;" class="s3abgimage" href="<?php echo $this->pluginUrl; ?>/images/128-121.jpg"><img src="<?php echo $this->pluginUrl; ?>/images/128-121.jpg" width="40" height="40"/></a><a style="float:left; margin-right:10px;" class="s3abgimage" href="<?php echo $this->pluginUrl; ?>/images/128-159.jpg"><img src="<?php echo $this->pluginUrl; ?>/images/128-159.jpg" width="40" height="40"/></a></td>
      </tr>
      
      <tr>
        <th scope="row" valign="top"><label for="radius">Border Radius:</label></th>
        <td><input type="text"  name="radius" id="radius" value="<?php echo $radius; ?>"/></td>
      </tr>
 <tr>
        <th scope="row" valign="top"><label for="jtoggle">Toggle Jquery Include:</label></th>
        <td><select name="jtoggle" id="jtoggle">
            <option value="<?php echo $jtoggle; ?>"><?php echo $jtoggle; ?></option>
            <option value="true">true</option>
            <option value="false">false</option>
          </select>
          <small>If plugin is not showing try this option.</small></td>
      </tr>
    </table>
    <br/>
    <span class="submit" style="border: 0;">
    <input type="submit" name="submit" value="Save Settings" />
    </span>
  </form>
  <p><br /></p>  
  <br />
  <h3>If you would like to put this feed within your template please use the following code</h3>
  <code>&lt;?php $soaudible = new soaudible(); $soaudible->soaudible_player(array('autoplay' => 'false', 'album' => 'hello')); ?&gt;</code>
  <p>Put it in your sidebar.php or anywhere within your theme</p>
  <h3>If you would like to put this feed within a post or page use the following code.</h3>
  <code>[soaudible autoplay="true" album="enter-your-album"]</code>
  <p style="width:100%; height:15px; display:block; clear:both;"></p>
 
  <iframe width="420" height="315" src="http://www.youtube.com/embed/QWwdNk16Og4" frameborder="0" allowfullscreen></iframe>
   <p style="width:100%; height:15px; display:block; clear:both;"></p>
  <p>This plugin was developed by soaudible if you need any help please <a href="mailto:support@soaudible.com" target="_blank">contact us</a></p>
  
<?php	
       } 
	  
	   function soaudible_player($atts){ 
	   
            // get option from database
			$soaudible_username = get_option("s3-soaudible_username");
			$soaudible_email = get_option("s3-soaudible_email");		
		   	$colour	         = get_option("s3-colour");
			$width	         = get_option("s3-width");
			$bgimage	     = get_option("s3-bgimage");
			$radius	         = get_option("s3-radius");
			
			
            // updated css
		    echo '<style type="text/css">.jp-type-playlist {
					background-color:'.stripcslashes($colour).';
					background-image:url('.stripcslashes($bgimage).');
					background-repeat:repeat;
					width: '.$width.';
					height: auto;
					position:relative;
					border-radius: '.stripcslashes($radius).' !important;
					-moz-border-radius: '.stripcslashes($radius).' !important;
					-webkit-border-radius: '.stripcslashes($radius).' !important;
					border: 1px solid #BFDADA;
					min-height:100px;
					} 
					.jp-interface {
					position: relative;
					width: 100%;
					border-top-left-radius: '.stripcslashes($radius).' !important;
					border-top-right-radius: '.stripcslashes($radius).' !important;
					-moz-border-top-left-radius: '.stripcslashes($radius).' !important;
					-moz-border-top-right-radius: '.stripcslashes($radius).' !important;
					-webkit-border-top-left-radius: '.stripcslashes($radius).' !important;
					-webkit-border-top-right-radius: '.stripcslashes($radius).' !important;
					}
					</style>';
			// jplayer html	
		   echo '<div class="soaudible" data-username="'.$soaudible_username.'" data-email="'.$soaudible_email.'" data-album="'.$atts['album'].'" data-autoplay="'.$atts['autoplay'].'"></div>';
		
        }

	}
// Initiate the class
$soaudible = new soaudible();
add_action( 'widgets_init', create_function( '', 'register_widget( "soaudible_widget" );' ) );
} //End Class soaudible

/**
 * Adds Foo_Widget widget.
 */
class soaudible_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'soaudible_widget', // Base ID
			'soaudible', // Name
			array( 'description' => __( 'soaudible Cloud Player', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$autoplay = apply_filters( 'autoplay', $instance['autoplay'] );
		$s3folder = $instance['s3folder'];
	
		echo $before_widget;
           
		    // get option from database
			$soaudible_username = get_option("s3-soaudible_username");
			$soaudible_email = get_option("s3-soaudible_email");		
		   	$colour	         = get_option("s3-colour");
			$width	         = get_option("s3-width");
			$bgimage	     = get_option("s3-bgimage");
			$radius	         = get_option("s3-radius");
	
            // updated css
		     echo '<style type="text/css">.jp-type-playlist {
					background-color:'.stripcslashes($colour).';
					background-image:url('.stripcslashes($bgimage).');
					background-repeat:repeat;
					width: '.$width.';
					height: auto;
					position:relative;
					border-radius: '.stripcslashes($radius).' !important;
					-moz-border-radius: '.stripcslashes($radius).' !important;
					-webkit-border-radius: '.stripcslashes($radius).' !important;
					border: 1px solid #BFDADA;
					min-height:100px;
					} 
					.jp-interface {
					position: relative;
					width: 100%;
					border-top-left-radius: '.stripcslashes($radius).' !important;
					border-top-right-radius: '.stripcslashes($radius).' !important;
					-moz-border-top-left-radius: '.stripcslashes($radius).' !important;
					-moz-border-top-right-radius: '.stripcslashes($radius).' !important;
					-webkit-border-top-left-radius: '.stripcslashes($radius).' !important;
					-webkit-border-top-right-radius: '.stripcslashes($radius).' !important;
					}

					
					</style>';
			// jplayer html	
		     echo '<div class="soaudible" data-username="'.$soaudible_username.'" data-email="'.$soaudible_email.'" data-album="'.$s3folder.'" data-autoplay="'.$autoplay.'"></div>';
		
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update(  $new_instance, $old_instance  ) {
		$instance = $old_instance;
		$instance = array();
		$instance['autoplay'] = strip_tags( $new_instance['autoplay'] );
		$instance['s3folder'] = strip_tags( $new_instance['s3folder'] );
		return $instance;

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'autoplay' ] ) ) {
			$autoplay = $instance[ 'autoplay' ];
		}
		else {
			$autoplay = __( 'false', 'text_domain' );
		}
		if ( isset( $instance[ 's3bucket' ] ) ) {
			$s3bucket = $instance[ 's3bucket' ];
		}
		else {
			$s3bucket = __( 'enter bucket', 'text_domain' );
		}
		if ( isset( $instance[ 's3folder' ] ) ) {
			$s3folder = $instance[ 's3folder' ];
		}
		else {
			$s3folder = __( '', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php _e( 'Autoplay:true/false' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" type="text" value="<?php echo esc_attr( $autoplay ); ?>" />
		</p>
         <p>
		<label for="<?php echo $this->get_field_id( 's3folder' ); ?>"><?php _e( 'Album' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 's3folder' ); ?>" name="<?php echo $this->get_field_name( 's3folder' ); ?>" type="text" value="<?php echo esc_attr( $s3folder ); ?>" />
		</p>
		<?php 
	}

} // class soaudible widget