<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.kickgdpr
 * @author      Niels Nübel <niels@kicktemp.com>
 * @author      Stefan Wendhausen <stefan@kicktemp.com>
 * @copyright   Copyright © 2019 Kicktemp UG (haftungsbeschränkt). All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @link        https://kicktemp.com
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  1.0
 */
class PlgSystemKickGdprScript extends JInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '3.8';
		$this->minimumPhp    = '7.0';
	}
}