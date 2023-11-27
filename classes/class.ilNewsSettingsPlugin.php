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

use ILIAS\DI\Container;
use ILIAS\Plugin\NewsSettings\GUI\Administration\Settings;

class ilNewsSettingsPlugin extends ilEventHookPlugin
{
    private const CTYPE = 'Services';
    private const CNAME = 'EventHandling';
    private const SLOT_ID = 'evhk';
    private const PNAME = 'NewsSettings';

    private static ?ilNewsSettingsPlugin $instance = null;
    protected static bool $initialized = false;
    /** @var int[] */
    protected static array $created_obj_ids = [];
    /** @var Container */
    protected $dic;

    public function __construct()
    {
        global $DIC;
        global $ilDB;
        $this->dic = $DIC;
        $cr = $DIC['component.repository'];
        parent::__construct($ilDB, $cr, "objnewsefaultset");
    }

    protected function init(): void
    {
        parent::init();
        $this->registerAutoloader();
        if (!self::$initialized) {
            self::$initialized = true;
            $this->dic['plugin.newssettings.settings'] = function (Container $c): Settings {
                return new Settings(
                    new ilSetting($this->getId())
                );
            };
        }
    }


    public function registerAutoloader(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    /**
     * returns a instance of IlNewsSettingsPlugin
     * @return static
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            global $DIC;
            $cf = $DIC['component.factory'];
            self::$instance = $cf->getPlugin("objnewsefaultset");
        }
        return self::$instance;
    }

    /**
     * Is called on every event in ILIAS
     * When the event matches the defined parameters it allowes for changes in the behaviour of ILIAS
     * @param string $component
     * @param string $event
     * @param array  $parameter
     * @return void
     */
    public function handleEvent(string $component, string $event, array $parameter): void
    {
        if (
            'Services/Object' === $component
            && 'create' === $event
            && isset($parameter['obj_id'])
        ) {
            self::$created_obj_ids[] = (int) $parameter['obj_id'];
            return;
        }

        if (
            'Services/Object' === $component
            && 'putObjectInTree' === $event
            && isset($parameter['object'])
            && (
                isset($parameter['obj_id'])
                && in_array((int) $parameter['obj_id'], self::$created_obj_ids, true)
            )
        ) {
            /** @var ilObject $object */
            $object = $parameter['object'];
            /** @var Settings $plugin_settings */
            $plugin_settings = $this->dic['plugin.newssettings.settings'];

            if (
                in_array($object->getType(), $this->getValidObjectTypes(), true)
                && $plugin_settings->isNewsEnabledFor($object->getType())
            ) {
                $object->setUseNews(true);
                if ($plugin_settings->isTimelineEnabledFor($object->getType())) {
                    $object->setNewsTimeline(true);
                }
                if ($plugin_settings->isTimelineAutoEntryEnabledFor($object->getType())) {
                    $object->setNewsTimelineAutoEntries(true);
                }
                if (basename($_SERVER['PHP_SELF']) !== 'server.php') {
                    $object->update();
                }
            }
        }
    }

    /**
     * List for selector in GUI
     * @return string[]
     */
    public function getValidObjectTypes(): array
    {
        return [
            'crs',
            'grp',
        ];
    }

    /**
     * Returns the plugin name
     */
    public function getPluginName(): string
    {
        return self::PNAME;
    }
}
