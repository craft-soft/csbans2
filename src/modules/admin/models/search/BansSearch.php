<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use app\models\Comment;
use app\models\File;
use yii\data\ActiveDataProvider;
use app\models\Ban;

/**
 * BansSearch represents the model behind the search form of `app\modules\admin\models\Ban`.
 */
class BansSearch extends Ban
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['bid', 'ban_kicks'], 'integer'],
            [['player_ip', 'player_id', 'player_nick', 'admin_ip', 'admin_id', 'admin_nick', 'ban_type',
                'ban_reason', 'server_ip', 'server_name', 'active', 'ban_created'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @noinspection DuplicatedCode
     */
    public function search(array $params): ActiveDataProvider
    {
        $filesCountQuery = File::find()->select('COUNT(*)')->alias('file')->where('ban.bid = file.bid');
        $commentsCountQuery = Comment::find()->select('COUNT(*)')->alias('comment')->where('ban.bid = comment.bid');
        $query = Ban::find()->alias('ban')
            ->select([
                'ban.*',
                'files_count' => $filesCountQuery,
                'comments_count' => $commentsCountQuery,
            ])
            ->joinWith('server server');

        $activeQuery = '([[ban]].[[expired]] = 0 AND ([[ban]].[[ban_created]] + (COALESCE([[server]].[[timezone_fixx]], 0) * 3600) + ([[ban]].[[ban_length]] * 60)) > UNIX_TIMESTAMP())';
        $inactiveQuery = '([[ban]].[[expired]] = 1 OR ([[ban]].[[ban_created]] + (COALESCE([[server]].[[timezone_fixx]], 0) * 3600) + ([[ban]].[[ban_length]] * 60)) < UNIX_TIMESTAMP())';
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['ban_created' => SORT_DESC],
                'attributes' => [
                    'bid',
                    'player_nick',
                    'player_id',
                    'player_ip',
                    'admin_nick',
                    'ban_created',
                    'ban_type',
                    'server_name',
                    'ban_reason',
                    'ban_length',
                    'ban_kicks',
                    'files_count' => [
                        'asc' => [
                            "({$filesCountQuery->createCommand()->getRawSql()})" => SORT_ASC,
                        ],
                        'desc' => [
                            "({$filesCountQuery->createCommand()->getRawSql()})" => SORT_DESC,
                        ],
                    ],
                    'comments_count' => [
                        'asc' => [
                            "({$commentsCountQuery->createCommand()->getRawSql()})" => SORT_ASC,
                        ],
                        'desc' => [
                            "({$commentsCountQuery->createCommand()->getRawSql()})" => SORT_DESC,
                        ],
                    ],
                    'active' => [
                        'asc' => [
                            $activeQuery => SORT_ASC,
                        ],
                        'desc' => [
                            $activeQuery => SORT_DESC,
                        ],
                    ],
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ban.bid' => $this->bid,
            "FROM_UNIXTIME(ban.ban_created, '%d.%m.%Y')" => $this->ban_created,
            'ban.ban_kicks' => $this->ban_kicks,
            'ban.server_name' => $this->server_name,
            'ban.ban_type' => $this->ban_type,
        ]);
        if ($this->active === '0' || $this->active === '1') {
            if ($this->active) {
                $query->andWhere($activeQuery);
            } else {
                $query->andWhere($inactiveQuery);
            }
        }

        $query->andFilterWhere(['like', 'ban.player_ip', $this->player_ip])
            ->andFilterWhere(['like', 'ban.player_id', $this->player_id])
            ->andFilterWhere(['like', 'ban.player_nick', $this->player_nick])
            ->andFilterWhere(['like', 'ban.admin_ip', $this->admin_ip])
            ->andFilterWhere(['like', 'ban.admin_id', $this->admin_id])
            ->andFilterWhere(['like', 'ban.admin_nick', $this->admin_nick])
            ->andFilterWhere(['like', 'ban.ban_reason', $this->ban_reason])
            ->andFilterWhere(['like', 'ban.server_ip', $this->server_ip])
            ->andFilterWhere(['like', 'ban.server_name', $this->server_name]);

        return $dataProvider;
    }

    public function serversForFilter(): array
    {
        return self::find()->select('server_name')->indexBy('server_name')->column();
    }
}
