=== IPVenger ===
Contributors: sburkett 
Tags: security, ipviking, block, dangerous, IP
Requires at least: 3.4.0
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv2 or later

IPVenger protects your WordPress installation by blocking access by IPs associated with high-risk behavior.

== Description ==

IPVenger uses NorseCorp IPViking real-time threat assessment technology to evaluate each IP requesting access to your WordPress site.

When a request comes in, the IP address is sent to the IPViking server, which assigns a numerical risk factor based on real-time data.

IPs whose risk factor exceeds a user-configurable threshold are blocked before they can harm your site. 

Detailed analytics are provided, as is the ability to blacklist specific IP addresses and countries of origin. 

== Installation ==

To install the beta IPVenger plugin on your WordPress site you will need the following before getting started:
1) A zip file of the plugin
2) A license key
3) Administrative access to your WordPress site
4) FTP access to your WordPress site may be required to complete the installation if:
	● You have moved your wp-config.php to a non-standard location
	● You have installed certain add-on security plugins
	● Your web server does not have permission to write to your wp-config.php file


SYSTEM REQUIREMENTS
● Browser Requirements
	○ Firefox and Chrome
	○ Internet Explorer version 9
	○ Interent Explorer 8+ (IE8 will lose minor functionality).

● WordPress Version(s)
	○ IPVenger is currently compatible and tested with the following versions of WordPress. Please confirm that your site(s) is running on one of the following versions of WordPress BEFORE you install 
		■ 3.4.0
		■ 3.4.1
		■ 3.4.2
● Only the MySQL database is currently supported

INSTALLATION STEPS
● Login to your WordPress site
● Select "Plugins" > "Add New" from the admin menu
● Click "Upload"
● Browse and select the IPVenger installation zip file that you have been provided
● Click "Install Now"
● Activate the Plugin
● Go to the IPVenger 'General Settings' page within your WordPress admin menu
● If you see "Activation Error" this means you will need FTP access to manually edit your wp-config.ph file. Follow the instructions shown on the error page, then reload the page and continue.
● Enter your license key and select "Activate" (activation will take a few seconds)
● Select the type of site that best describes the main function of your website
● Select who you want block notifications to go to, under 'Email Settings'

SPECIAL INSTRUCTIONS FOR CUSTOM WP-CONFIG
IPVenger must update wp-config.php in order to block dangerous sites. In most cases, it can do this automatically, but if your web server does not have write access to wp-config, you will need to do the update manually.

If this update is required you will see an “Activation Error” and the changes you need to make to wp-config. The page is reproduced below for completeness:

	ACTIVATION ERROR
	IPVenger could not update wp-config.php. This is most likely because your web server does not have permission to write to this file. To complete the installation and activate IPVenger security you must update the file manually.

	Open your wp-config.php file and locate the line that says:
      		"/* That's all, stop editing! Happy blogging. */"

	Above this line, insert the following text, exactly as displayed below, then reload this page to continue.

      		/*** BEGIN IPVENGER CODE BLOCK ***/
      		$validate_include = dirname(__FILE__) .  '/wp-content/plugins' .
         		'/ipvenger/core-includes/ipv_websec_validate.php';
      		if ( file_exists ( $validate_include ) ) {
         		require_once( $validate_include ); 
	 		ipv_websec_gatekeeper();
      		}
      		/*** END IPVENGER CODE BLOCK ***/

