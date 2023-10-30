<?php
/**
 * @package          Joomla
 * @subpackage       SchuWeb_Sitemap
 *
 * @author           Sven Schultschik <extensions@schultschik.com>
 * @copyright    (c) 2019 - 2023 extensions.schultschik.com - All rights reserved
 * @license          GNU General Public License version 3 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 *
 * @copyright   Copyright (C) 2019 - 2022 Sven Schultschik. All rights reserved
 *              reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since
 */
class pkg_schuweb_sitemapInstallerScript extends InstallerScript
{

    /**
     * Extension script constructor.
     *
     * @since   4.0.0
     */
    public function __construct()
    {
        // Define the minumum versions to be supported.
        $this->minimumJoomla = '4.0';
        $this->minimumPhp = '8';
    }

	/**
	 * Runs right after any installation action is performed on the component.
	 *
	 * @param   string     $type    - Type of PostFlight action. Possible values
	 *                              are:
	 *                              - * install
	 *                              - * update
	 *                              - * discover_install
	 * @param   \stdClass  $parent  - Parent object calling object.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since       __DEPLOY_VERSION__
	 */
	public function postflight($type, $parent)
	{
		if ($type == "update")
			$this->upgradeJ3J4();

		if ($type != "install")
		{
			return;
		}

		// Which components are installed?
		require_once JPATH_ADMINISTRATOR
			. DIRECTORY_SEPARATOR . 'components'
			. DIRECTORY_SEPARATOR . 'com_schuweb_sitemap'
			. DIRECTORY_SEPARATOR . 'helpers'
			. DIRECTORY_SEPARATOR . 'schuweb_sitemap.php';

		$extensions = SchuWeb_SitemapHelper::getExtensionsList();

		$db = Factory::getDbo();

		// Activate the fitting plugins
		foreach ($extensions as $extension)
		{
			$query = $db->getQuery(true);

			$fields = array(
				$db->quoteName('enabled') . '= 1',
			);

			$conditions = array(
				$db->quoteName('type') . '=' . $db->quote('plugin'),
				$db->quoteName('element') . '=' . $db->quote($extension->element),
			);

			$query->update($db->quoteName('#__extensions'))
				->set($fields)
				->where($conditions);


			$db->setQuery($query);

			if (!$db->execute())
			{
				Factory::getApplication()->enqueueMessage(JText::_('SCHUWEB_SITEMAP_ACTIVATE_PLUGIN_FAILED'), 'error');
			}
		}

		// Get all menus
		$query = $db->getQuery(true);

		$query->select($db->quoteName('menutype'))
			->from($db->quoteName('#__menu_types'));

		$db->setQuery($query);
		$menus = $db->loadObjectList();

		// Create a default sitemap with all needed components and all menus
		$columns = array(
			'title',
			'alias',
			'attribs',
			'selections',
			'is_default',
			'state',
			'access',
		);

		// {"mainmenu":{"enabled":"1","priority":"0.5","changefreq":"weekly"}}
		$selections = array();

		foreach ($menus as $menu)
		{
			$selections[$menu->menutype] = array(
				"enabled"    => 1,
				"priority"   => 1,
				"changefreq" => "weekly",
			);
		}

		$attribs = '{"showintro":"1","show_menutitle":"1","classname":"",'
			. '"columns":"","compress_xml":"1",'
			. '"include_link":"1","xmlLastMod":"1",'
			. '"xmlInsertChangeFreq":"1","xmlInsertPriority":"1",'
			. '"news_publication_name":""}';

		$values = array(
			$db->quote('Sitemap'),
			$db->quote('sitemap'),
			$db->quote($attribs),
			$db->quote(json_encode($selections)),
			1,
			1,
			1,
		);

		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__schuweb_sitemap'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * This method should handle the steps if some has upgrade from J3 to J4
	 *
	 * @since 3.4.0
	 */
	private function upgradeJ3J4()
	{
		$errMessages = array();

		if (version_compare(JVERSION, '4', 'ge'))
		{
			$unsupported = array('com_sobipro', 'com_virtuemart');
			foreach ($unsupported as $componentName)
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);

				$query->select('*')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . '=' . $db->q($componentName))
					->where($db->qn('folder') . '=' . $db->q('schuweb_sitemap'));

				$db->setQuery($query);
				$extension = $db->loadObject();

				if (!is_null($extension))
				{
					$manifest = json_decode($extension->manifest_cache);

					if (!is_null($manifest))
					{
						if (version_compare($manifest->version, '3.4.0', 'lt'))
						{
							$installer = Installer::getInstance();
							if (!$installer->uninstall('plugin', $extension->extension_id))
								$errMessages[] = Text::sprintf('COM_SCHUWEB_SITEMAP_POSTFLIGHT_PLUGIN_UNINSTALL_ERR', $componentName);
						}
					}
					else
					{
						$errMessages[] = Text::sprintf('COM_SCHUWEB_SITEMAP_POSTFLIGHT_PLUGIN_UNINSTALL_MANIFEST_ERR', $componentName);
					}
				}
			}
		}

		$this->printError($errMessages);
	}

	private function printError($messages)
	{
		if (empty($messages))
			return;

		echo '<div class="alert alert-error">';
		foreach ($messages as $message){
			echo '<p>'.$message.'</p>';
		}
		echo '</div>';
	}
}
