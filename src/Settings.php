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

use ilSetting;

class Settings
{
    private ilSetting $settings;
    private array $news_by_object_type = [];

    /**
     * @param ilSetting $settings
     */
    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
        $this->read();
    }

    /**
     * @return void
     */
    private function read(): void
    {
        $news_by_object_type = $this->settings->get('news_by_obj_type', null);
        if ($news_by_object_type !== null && $news_by_object_type !== '') {
            $news_by_object_type = json_decode($news_by_object_type, true);
        }
        if (!is_array($news_by_object_type)) {
            $news_by_object_type  = [];
        }

        $this->news_by_object_type = $news_by_object_type;
    }

    /**
     * news setting, needed for news blocks(legacy/other plugin? I don't see them in a ILIAS 7 install) and timeline
     * @param string $obj_type
     * @param bool $status
     * @return void
     */
    public function setNewsStatusFor(string $obj_type, bool $status): void
    {
        $this->news_by_object_type[$obj_type]['news'] = $status;
    }

    /**
     * @param string $obj_type
     * @return bool
     */
    public function isNewsEnabledFor(string $obj_type): bool
    {
        return $this->news_by_object_type[$obj_type]['news'] ?? false;
    }

    /**
     * show timeline in object type
     * @param string $obj_type
     * @param bool $status
     * @return void
     */
    public function setTimelineStatusFor(string $obj_type, bool $status): void
    {
        $this->news_by_object_type[$obj_type]['timeline'] = $status;
    }

    /**
     * @param string $obj_type
     * @return bool
     */
    public function isTimelineEnabledFor(string $obj_type): bool
    {
        return $this->news_by_object_type[$obj_type]['timeline'] ?? false;
    }

    /**
     * allows the timeline to include automatic entries
     * @param string $obj_type
     * @param bool $status
     * @return void
     */
    public function setTimelineAutoEntryStatusFor(string $obj_type, bool $status): void
    {
        $this->news_by_object_type[$obj_type]['timeline_auto_entry'] = $status;
    }

    /**
     * @param string $obj_type
     * @return bool
     */
    public function isTimelineAutoEntryEnabledFor(string $obj_type): bool
    {
        return $this->news_by_object_type[$obj_type]['timeline_auto_entry'] ?? false;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $this->settings->set('news_by_obj_type', json_encode($this->news_by_object_type));
    }
}
