<?php

if (!class_exists('Securimage', true)) {
    require_once dirname(__FILE__) . '/securimage.php';
}

if (!class_exists('Securimage', false)) {
    die('Securimage class not found.');
}

/**
 * Securimage CAPTCHA Class.
 *
 * @version    3.0
 * @package    Securimage
 * @subpackage classes
 * @author     Jason Belich <jb@norse-corp.com>
 *
 */
class Securimage_Cache extends Securimage
{

    /**
     * Sends the appropriate image and cache headers and outputs image to the browser
     */
    protected function output()
    {

		ob_start();
		imagepng($this->im);
		$image_data = ob_get_clean();

		ipv_insert_captcha( $image_data, $this->code );

        imagedestroy($this->im);
        restore_error_handler();

    }

}
