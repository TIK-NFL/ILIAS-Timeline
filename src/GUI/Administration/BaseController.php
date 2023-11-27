<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

namespace ILIAS\Plugin\NewsSettings\GUI\Administration;

use ilCtrl;
use ilCtrlException;
use ilGlobalTemplateInterface;
use ILIAS\DI\Container;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilNewsSettingsApplyConfigGUI;
use ilNewsSettingsConfigGUI;
use ilNewsSettingsPlugin;
use ilObjComponentSettingsGUI;
use ilPlugin;
use ilPluginConfigGUI;
use ilSetting;
use ilTabsGUI;
use ilUtil;

abstract class BaseController extends ilPluginConfigGUI
{
    private \ILIAS\Refinery\Factory $refinery;
    /** @var ilCtrl */
    protected $ctrl;
    /** @var Container */
    protected $dic;
    /** @var GlobalHttpState */
    protected $http;

    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $page_template;
    public ilSetting $settings;
    protected ?ilPlugin $plugin_object;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected Settings $plugin_settings;
    protected ilGlobalTemplateInterface $tpl;

    /**
     * @param ilNewsSettingsPlugin|null $plugin
     */
    public function __construct(ilNewsSettingsPlugin $plugin = null)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->page_template = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC['tpl'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->http = $DIC->http();

        $this->plugin_object = $plugin;
        if (!$this->plugin_object instanceof ilNewsSettingsPlugin) {
            $this->plugin_object = ilNewsSettingsPlugin::getInstance();
        }
    }

    /**
     * @param string $class
     * @param string $parameter
     * @return void
     * @throws ilCtrlException
     */
    private function setCtrlParameterFromQuery(string $class, string $parameter): void
    {
        if (
            isset($this->http->request()->getQueryParams()[$parameter])
            && is_string($this->http->request()->getQueryParams()[$parameter])
        ) {
            $this->ctrl->setParameterByClass(
                $class,
                $parameter,
                ilUtil::stripSlashes($this->http->request()->getQueryParams()[$parameter])
            );
        }
    }

    /**
     * @param string $class
     * @param string $parameter
     * @return void
     * @throws ilCtrlException
     */
    private function setCtrlParameterFromBody(string $class, string $parameter): void
    {
        if (
            isset($this->http->request()->getParsedBody()[$parameter])
            && is_string($this->http->request()->getParsedBody()[$parameter])
        ) {
            $this->ctrl->setParameterByClass(
                $class,
                $parameter,
                ilUtil::stripSlashes($this->http->request()->getParsedBody()[$parameter])
            );
        }
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        foreach (['ctype', 'cname', 'slot_id', 'plugin_id', 'pname'] as $parameter) {
            $this->setCtrlParameterFromQuery(static::class, $parameter);
        }
        $this->page_template->setTitle(
            $this->lng->txt('cmps_plugin') . ': ' . ilUtil::stripSlashes($this->http->request()->getQueryParams()['pname'])
        );
        $this->page_template->setDescription('');
        $this->performCommand($this->ctrl->getCmd());
        $this->showTabs();
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function showTabs(): void
    {
        $this->tabs->clearTargets();
        foreach ([ilObjComponentSettingsGUI::class, ilNewsSettingsConfigGUI::class] as $controller_class) {
            foreach (['ctype', 'cname', 'slot_id', 'plugin_id', 'pname'] as $parameter) {
                $this->setCtrlParameterFromQuery($controller_class, $parameter);
            }
        }

        $this->showBackTargetTab();
        $this->tabs->addTab(
            'configuration_presets',
            $this->plugin_object->txt('configuration_presets'),
            $this->ctrl->getLinkTargetByClass(ilNewsSettingsConfigGUI::class)
        );
        $this->tabs->addTab(
            'modify_settings',
            $this->plugin_object->txt('modify_settings'),
            $this->ctrl->getLinkTargetByClass(ilNewsSettingsApplyConfigGUI::class)
        );
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function showBackTargetTab(): void
    {
        if (isset($this->http->request()->getQueryParams()['plugin_id'])) {
            $this->tabs->setBackTarget(
                $this->lng->txt('cmps_plugin'),
                $this->ctrl->getLinkTargetByClass(ilObjComponentSettingsGUI::class, 'showPlugin')
            );
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt('cmps_plugins'),
                $this->ctrl->getLinkTargetByClass(ilObjComponentSettingsGUI::class, 'listPlugins')
            );
        }
    }

    /**
     * @param string $cmd
     * @return void
     */
    public function performCommand(string $cmd): void
    {
        $this->plugin_settings = $this->dic['plugin.newssettings.settings'];

        if (true === method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            $this->{$this->getDefaultCommand()}();
        }
    }

    /**
     * @return string
     */
    abstract protected function getDefaultCommand(): string;

}
