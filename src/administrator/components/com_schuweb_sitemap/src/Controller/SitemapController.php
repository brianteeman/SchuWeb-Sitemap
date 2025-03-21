<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2019 - 2025 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Component\Sitemap\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller for a single sitemap
 *
 * @since  5.0.0
 */
class SitemapController extends FormController
{
    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  1.6
     */
    protected $option = "com_schuweb_sitemap";

}