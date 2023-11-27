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

use ILIAS\Plugin\NewsSettings\GUI\Administration\BaseController;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @ilCtrl_Calls ilNewsSettingsConfigGUI: ilNewsSettingsApplyConfigGUI
 * @ilCtrl_IsCalledBy ilNewsSettingsConfigGUI: ilObjComponentSettingsGUI
 */
class ilNewsSettingsConfigGUI extends BaseController
{
    public function __construct(ilNewsSettingsPlugin $plugin = null)
    {
        parent::__construct($plugin);
    }

    protected function getDefaultCommand(): string
    {
        return 'showPresetConfiguration';
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        switch (strtolower($next_class)) {
            case strtolower(ilNewsSettingsApplyConfigGUI::class):
                $this->ctrl->forwardCommand(
                    new ilNewsSettingsApplyConfigGUI(
                        $this->plugin_object
                    )
                );
                $this->tabs->activateTab('modify_settings');
                break;

            default:
                parent::executeCommand();
                $this->tabs->activateTab('configuration_presets');
                break;
        }
    }

    /**
     * @return Standard
     * @throws ilCtrlException
     */
    protected function getPresetForm(): ILIAS\UI\Implementation\Component\Input\Container\Form\Standard
    {
        $checkbox_course_enable = $this->ui_factory->input()->field()->checkbox(
            $this->plugin_object->txt('news_setting_timeline_enable'),
            $this->plugin_object->txt('news_setting_timeline_byline')
        );
        $checkbox_course_inclusion = $this->ui_factory->input()->field()->checkbox(
            $this->plugin_object->txt('news_setting_timeline_include_automatic_entries_enable'),
            $this->plugin_object->txt('news_setting_timeline_include_automatic_entries_byline')
        );
        $checkbox_group_enable = $this->ui_factory->input()->field()->checkbox(
            $this->plugin_object->txt('news_setting_timeline_enable'),
            $this->plugin_object->txt('news_setting_timeline_byline')
        );
        $checkbox_group_inclusion = $this->ui_factory->input()->field()->checkbox(
            $this->plugin_object->txt('news_setting_timeline_include_automatic_entries_enable'),
            $this->plugin_object->txt('news_setting_timeline_include_automatic_entries_byline')
        );
        $checkbox_group_course = $this->ui_factory->input()->field()->optionalGroup(
            [$checkbox_course_enable, $checkbox_course_inclusion],
            $this->plugin_object->txt('news_setting_timeline_checkbox_group_course'),
            $this->plugin_object->txt('news_setting_timeline_checkbox_group_byline')
        );
        $checkbox_group_group = $this->ui_factory->input()->field()->optionalGroup(
            [$checkbox_group_enable, $checkbox_group_inclusion],
            $this->plugin_object->txt('news_setting_timeline_checkbox_group_group'),
            $this->plugin_object->txt('news_setting_timeline_checkbox_group_byline')
        );
        $section_content = [
            $checkbox_group_course,
            $checkbox_group_group
        ];
        $section = $this->ui_factory->input()->field()->section(
            $section_content,
            $this->plugin_object->txt('news_setting_timeline_tab_presets'),
        );
        $form_action = $this->dic->ctrl()->getFormActionByClass(ilNewsSettingsConfigGUI::class, 'savePresetConfiguration');
        $form = $this->ui_factory->input()->container()->form()->standard($form_action, [$section]);
        // autofill information
        $request = $this->dic->http()->request();
        if($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
        }
        return $form;
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function showPresetConfiguration(): void
    {
        $form = $this->getPresetForm();
        $this->page_template->setContent($this->ui_renderer->render($form));
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    protected function savePresetConfiguration(): void
    {
        $form = $this->getPresetForm();
        $results = $form->getData();
        if (!empty($results)) {
            $types = $this->plugin_object->getValidObjectTypes();
            foreach ($results as $key => $value) {
                $this->plugin_settings->setNewsStatusFor(
                    $types[$key],
                    !is_null($value)
                );
                $this->plugin_settings->setTimelineStatusFor(
                    $types[$key],
                    $value[0] ?? false
                );
                $this->plugin_settings->setTimelineAutoEntryStatusFor(
                    $types[$key],
                    $value[1] ?? false
                );
            }
            $this->plugin_settings->save();
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        }
        $this->page_template->setContent($this->ui_renderer->render($form));
    }

    /**
     * transfers the settings into the form
     * @param ilPropertyFormGUI $form
     * @return void
     */
    protected function populateValues(ilPropertyFormGUI $form): void
    {
        $data = [];
        foreach ($this->getPluginObject()->getValidObjectTypes() as $objectType) {
            $data['news_status_' . $objectType] = $this->plugin_settings->isNewsEnabledFor($objectType);
            $data['timeline_status_' . $objectType] = $this->plugin_settings->isTimelineEnabledFor($objectType);
            $data['timeline_auto_entry_status_' . $objectType] = $this->plugin_settings->isTimelineAutoEntryEnabledFor($objectType);
        }
        $form->setValuesByArray($data);
    }
}
