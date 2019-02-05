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


class PlgSystemKickGDPRInstallerScript
{
	private $min_joomla_version      = '3.8.0';
	private $min_php_version         = '7.0';
	private $name                    = 'System Plugin KickGDPR';
	private $extname                 = 'plg_system_kickgdpr';
	private $previous_version        = '';
	private $previous_version_simple = '';
	private $dir = null;

	public function __construct()
	{
		$this->dir = __DIR__;
	}

	public function postflight($route, $installer)
	{
		$changelog = $this->getChangelog();

		JFactory::getApplication()->enqueueMessage($changelog, 'notice');

		return true;
	}

	private function getChangelog()
	{
		$changelog = file_get_contents($this->dir . '/CHANGELOG.txt');

		$changelog = "\n" . trim(preg_replace('#^.* \*/#s', '', $changelog));
		$changelog = preg_replace("#\r#s", '', $changelog);

		$parts = explode("\n\n", $changelog);

		if (empty($parts))
		{
			return '';
		}

		$this_version = '';

		$changelog = [];

		// Add first entry to the changelog
		$changelog[] = array_shift($parts);

		// Add extra older entries if this is an upgrade based on previous installed version
		if ($this->previous_version_simple)
		{
			if (preg_match('#^[0-9]+-[a-z]+-[0-9]+ : v([0-9\.]+(?:-dev[0-9]+)?)\n#i', trim($changelog[0]), $match))
			{
				$this_version = $match[1];
			}

			foreach ($parts as $part)
			{
				$part = trim($part);

				if ( ! preg_match('#^[0-9]+-[a-z]+-[0-9]+ : v([0-9\.]+(?:-dev[0-9]+)?)\n#i', $part, $match))
				{
					continue;
				}

				$changelog_version = $match[1];

				if (version_compare($changelog_version, $this->previous_version_simple, '<='))
				{
					break;
				}

				$changelog[] = $part;
			}
		}

		$changelog = implode("\n\n", $changelog);

		//  + Added   ! Removed   ^ Changed   # Fixed
		$change_types = [
			'+' => ['Added', 'success'],
			'!' => ['Removed', 'danger'],
			'^' => ['Changed', 'warning'],
			'#' => ['Fixed', 'info'],
		];
		foreach ($change_types as $char => $type)
		{
			$changelog = preg_replace(
				'#\n ' . preg_quote($char, '#') . ' #',
				"\n" . '<span class="label label-sm label-' . $type[1] . '" title="' . $type[0] . '">' . $char . '</span> ',
				$changelog
			);
		}

		$changelog = preg_replace('#see: (https://www\.kicktemp\.com[^ \)]*)#s', '<a href="\1" target="_blank">see documentation</a>', $changelog);

		$changelog = preg_replace(
			"#(\n+)([0-9]+.*?) : v([0-9\.]+(?:-dev[0-9]+)?)([^\n]*?\n+)#",
			'</pre>\1'
			. '<h3><span class="label label-inverse" style="font-size: 0.8em;">v\3</span>'
			. ' <small>\2</small></h3>'
			. '\4<pre>',
			$changelog
		);

		$changelog = str_replace(
			[
				'<pre>',
				'[FREE]',
				'[PRO]',
			],
			[
				'<pre style="line-height: 1.6em;">',
				'<span class="badge badge-sm badge-success">FREE</span>',
				'<span class="badge badge-sm badge-info">PRO</span>',
			],
			$changelog
		);

		$changelog = preg_replace(
			'#\[J([1-9][\.0-9]*)\]#',
			'<span class="badge badge-sm badge-default">J\1</span>',
			$changelog
		);

		$title = 'Latest changes for ' . JText::_($this->name);

		if ($this->previous_version_simple && version_compare($this->previous_version_simple, $this_version, '<'))
		{
			$title .= ' since v' . $this->previous_version_simple;
		}

		if ($this->previous_version_simple
			&& $this->getMajorVersionPart($this->previous_version_simple) < $this->getMajorVersionPart($this_version)
			&& ! $this->hasMessagesOfType('warning')
		)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('RLI_MAJOR_UPGRADE', JText::_($this->name)), 'warning');
		}

		return '<h3>' . $title . ':</h3>'
			. '<div style="max-height: 240px; padding-right: 20px; margin-right: -20px; overflow: auto;">'
			. $changelog
			. '</div>';
	}
}