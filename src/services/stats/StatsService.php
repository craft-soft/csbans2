<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\services\stats;

use app\models\{AmxAdmin, Ban, Comment, File, Server, Webadmin};

class StatsService
{
    private \DateTime $startDate;
    private \DateTime $endDate;

    /**
     * @param string $period
     */
    public function setPeriod(string $period)
    {
        $this->parsePeriod($period);
    }

    private function parsePeriod(string $period): void
    {
        $tmp = explode(',', $period);
        $start = $tmp[0];
        if (empty($tmp[1])) {
            $end = date('d.m.Y');
        } else {
            $end = $tmp[1];
        }
        $this->startDate = \DateTime::createFromFormat('d.m.Y', $start)->setTime(0, 0, 0);
        $this->endDate = \DateTime::createFromFormat('d.m.Y', $end);
        if ($end === date('d.m.Y')) {
            $this->endDate->setTime((int)date('G'), (int)date('i'), (int)date('s'));
        } else {
            $this->endDate->setTime(23, 59, 59);
        }
    }

    public function getBoxes(): Boxes
    {
        $newBans = Ban::find()
            ->where(['between', 'ban_created', $this->startDate->format('U'), $this->endDate->format('U')])
            ->count();
        $newFiles = File::find()
            ->where(['between', 'upload_time', $this->startDate->format('U'), $this->endDate->format('U')])
            ->count();
        $newComments = Comment::find()
            ->where(['between', 'date', $this->startDate->format('U'), $this->endDate->format('U')])
            ->count();
        $boxes = new Boxes(\Yii::t('admin/stats', 'BANS_BOXES_LABEL'));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'NEW_BANS_BOX_LABEL'),
            $newBans,
            'primary',
            'fas fa-user-slash',
        ));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'NEW_FILES_BOX_LABEL'),
            $newFiles,
            'info',
            'far fa-file-alt'
        ));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'NEW_COMMENTS_BOX_LABEL'),
            $newComments,
            'success',
            'far fa-comments'
        ));
        return $boxes;
    }

    public function getInfo(): Boxes
    {
        $boxes = new Boxes(\Yii::t('admin/stats', 'TOTAL_ITEMS_BOX_LABEL'));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'ACTIVE_BANS_BOX_LABEL'),
            $this->activeBans(),
            'danger',
            'fas fa-user-slash'
        ));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'TOTAL_AMX_ADMINS_BOX_LABEL'),
            $this->amxAdmins(),
            'primary',
            'fas fa-user-tie'
        ));
        $boxes->addBox(new Box(
            \Yii::t('admin/stats', 'TOTAL_WEB_ADMINS_BOX_LABEL'),
            (int)Webadmin::find()->count(),
            'success',
            'fas fa-user-shield'
        ));
        return $boxes;
    }

    public function amxAdmins(): int
    {
        return (int)AmxAdmin::find()->count();
    }

    public function servers(): int
    {
        return (int)Server::find()->count();
    }

    public function activeBans(): int
    {
        return (int)Ban::find()
            ->where(['expired' => 0])
            ->orWhere('([[ban_created]] + ([[ban_length]] * 60)) > UNIX_TIMESTAMP()')
            ->count();
    }

    public function expiredBans(): int
    {
        return (int)Ban::find()
            ->where(['expired' => 1])
            ->orWhere('([[ban_created]] + ([[ban_length]] * 60)) < UNIX_TIMESTAMP()')
            ->count();
    }

    public function permanentBans(): int
    {
        return (int)Ban::find()
            ->where(['ban_length' => 0])
            ->count();
    }

    public function temporaryBans(): int
    {
        return (int)Ban::find()
            ->where(['!=', 'ban_length', 0])
            ->count();
    }

    public function totalBans(): int
    {
        return (int)Ban::find()->count();
    }
}
