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

/**
 * @ilCtrl_Calls ilNewsSettingsApplyConfigGUI: ilNewsSettingsConfirmationGUI
 * @ilCtrl_IsCalledBy ilNewsSettingsApplyConfigGUI: ilNewsSettingsApplyConfigGUI
 */
class ilNewsSettingsApplyConfigGUI extends BaseController
{
    /**
     * @return string
     */
    protected function getDefaultCommand(): string
    {
        return 'showConfiguration';
    }


    /**
     * @return void
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        parent::executeCommand();
        $this->tabs->activateTab('modify_settings');
    }

    /**
     *
     * @return Standard
     * @throws ilCtrlException
     */
    protected function getForm(): ILIAS\UI\Implementation\Component\Input\Container\Form\Standard
    {

        //language
        $this->lng->loadLanguageModule('trac');
        $text_input = $this->ui_factory->input()->field()->text(
            $this->plugin_object->txt('news_setting_timeline_textfield_id')
        )
            ->withRequired(true);
        $section_content = [
            $text_input,
        ];
        foreach ($this->plugin_object->getValidObjectTypes() as $object_type) {
            $section_content[] = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('obj_' . $object_type)
            )
            ->withValue(true);
        }

        $section = $this->ui_factory->input()->field()->section(
            $section_content,
            $this->plugin_object->txt('news_setting_timeline_tab_existing_settings'),
            $this->plugin_object->txt('news_setting_timeline_modify_form_info')
        );

        $form_action = $this->dic->ctrl()->getFormActionByClass(ilNewsSettingsApplyConfigGUI::class, 'confirmApplyConfiguration');
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
    protected function showConfiguration(): void
    {
        $this->page_template->setContent($this->ui_renderer->render($this->getForm()));
    }

    /**
     * Confirmation page to shown before any change is done
     * @return void
     * @throws ilCtrlException
     */
    protected function confirmApplyConfiguration(): void
    {
        $form = $this->getForm();
        if ( !is_null($form->getData()) && !empty($form->getData()[0]) ) {
            $result = $form->getData()[0];
            $confirmation = new ilConfirmationGUI();
            $confirmation->setFormAction($this->ctrl->getFormAction($this, 'applyConfiguration'));
            $confirmation->setConfirm($this->lng->txt('confirm'), 'applyConfiguration');
            $confirmation->setCancel($this->lng->txt('cancel'), $this->getDefaultCommand());
            $object_input = [$result[1], $result[2]];
            $types = $this->plugin_object->getValidObjectTypes();
            $object_types = [];
            foreach ($object_input as $key => $object_type) {
                if ($object_type == '1') {
                    $confirmation->addHiddenItem('object_types[]', (string) $this->plugin_object->getValidObjectTypes()[$key]);
                    $object_types[] = $this->lng->txt('obj_' . $this->plugin_object->getValidObjectTypes()[$key]);
                }
            }

            $ref_id = [$result[0]];
            $confirmation->addHiddenItem(
                'enable_timeline_to_crs_and_grp_tree',
                $result[0]
            );
            $confirmation->setHeaderText(sprintf(
                $this->plugin_object->txt('news_setting_timeline_adopt_preset'),
                implode(', ', array_merge($object_types, $ref_id))
            ));

            $this->page_template->setContent($confirmation->getHTML());
            return;
        }

        $this->page_template->setContent($this->ui_renderer->render($form));
    }


    /**
     * Applies the configuration changes to the specified object types through the subtree.
     * @return void
     * @throws ilCtrlException
     */
    protected function applyConfiguration(): void
    {
        // get object types to apply changes to
        $object_types = array_intersect(
            (array) ($this->http->request()->getParsedBody()['object_types'] ?? []),
            $this->plugin_object->getValidObjectTypes()
        );
        // get ref_id of the subtree that is looked upon
        $ref_id = $this->http->request()->getParsedBody()['enable_timeline_to_crs_and_grp_tree'];

        foreach ($object_types as $type) {
            $this->dic->database()->manipulateF(
                "UPDATE container_settings 
                INNER JOIN object_reference ON container_settings.id = object_reference.obj_id     
                INNER JOIN tree ON object_reference.ref_id = tree.child   
                INNER JOIN object_data ON object_reference.obj_id = object_data.obj_id 
                        AND object_data.type = %s
                        AND (tree.path like '%%.%u.%%' OR tree.path like '%u.%%') 
                        AND object_reference.deleted is null
                        AND NOT container_settings.value = 1 
                        AND (container_settings.keyword = 'cont_use_news'
                            OR container_settings.keyword = 'news_timeline'
                            OR container_settings.keyword = 'news_timeline_incl_auto')
                SET container_settings.value = 1",
                ['text', 'integer', 'integer'],
                [$type, $ref_id, $ref_id]
            );
        }

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}
