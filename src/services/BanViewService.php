<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\services;

use yii\data\ActiveDataProvider;
use app\models\{Ban, Comment, File};

class BanViewService
{
    private Ban $ban;

    private bool $isFrontend;

    /**
     * @param Ban $ban
     * @param bool $isFrontend
     */
    public function __construct(Ban $ban, bool $isFrontend = false)
    {
        $this->ban = $ban;
        $this->isFrontend = $isFrontend;
    }

    public function viewParams(): array
    {
        return [
            'model' => $this->ban,
            'historyDataProvider' => $this->banHistoryProvider(),
            'commentsDataProvider' => $this->commentsProvider(),
            'filesDataProvider' => $this->filesProvider()
        ];
    }

    public function banHistoryProvider(): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Ban::find()->where([
                'and',
                ['!=', 'bid', $this->ban->bid],
                [
                    'or',
                    ['player_ip' => $this->ban->player_ip],
                    ['player_id' => $this->ban->player_id],
                ],
            ]),
            'sort' => [
                'defaultOrder' => ['ban_created' => SORT_DESC],
                'sortParam' => 'history-sort'
            ],
            'pagination' => [
                'pageParam' => 'ban-history-page',
                'pageSizeParam' => 'ban-history-per-page',
                'pageSize' => 10
            ]
        ]);
    }

    public function commentsProvider(): ActiveDataProvider
    {
        $query = Comment::find()->where(['bid' => $this->ban->bid]);
        if ($this->isFrontend) {
            $query->andWhere(['moderated' => 1]);
        }
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['date' => SORT_DESC],
                'sortParam' => 'comments-sort'
            ],
            'pagination' => [
                'pageParam' => 'ban-comments-page',
                'pageSizeParam' => 'ban-comments-per-page',
                'pageSize' => 10
            ]
        ]);
    }

    public function filesProvider(): ActiveDataProvider
    {
        $query = File::find()->where(['bid' => $this->ban->bid]);
        if ($this->isFrontend) {
            $query->andWhere(['moderated' => 1]);
        }
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['upload_time' => SORT_DESC],
                'sortParam' => 'files-sort'
            ],
            'pagination' => [
                'pageParam' => 'ban-files-page',
                'pageSizeParam' => 'ban-files-per-page',
                'pageSize' => 10
            ]
        ]);
    }

    /**
     * @param File|Comment $model
     * @return bool
     */
    public function contentNeedModerate($model): bool
    {
        if ($model->moderated) {
            return false;
        }
        return \Yii::$app->getUser()->getName() === $model->name;
    }
}
