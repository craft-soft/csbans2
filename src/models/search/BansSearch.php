<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\models\search;

use app\models\Comment;
use app\models\File;
use yii\data\ActiveDataProvider;
use app\models\Ban;

/**
 * BansSearch represents the model behind the search form of `app\modules\admin\models\Ban`.
 */
class BansSearch extends Ban
{
    public ?string $query = null;

    public ?string $field = null;

    private const SEARCH_FIELDS = ['player_ip', 'player_id', 'player_nick', 'ban_reason'];

    private const ACTIVE_BAN_QUERY = '([[ban]].[[expired]] = 0 AND ([[ban]].[[ban_created]] + (COALESCE([[server]].[[timezone_fixx]], 0) * 3600) + ([[ban]].[[ban_length]] * 60)) > UNIX_TIMESTAMP())';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['field', 'in', 'range' => self::SEARCH_FIELDS],
            ['query', 'safe'],
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
        $select = [
            'ban.*'
        ];
        $sortAttributes = [
            'bid',
            'player_nick',
            'player_id',
            'admin_nick',
            'ban_created',
            'ban_type',
            'server_name',
            'ban_reason',
            'ban_length',
        ];
        if (\Yii::$app->appParams->bans_view_kicks_count) {
            $sortAttributes[] = 'ban_kicks';
        }
        if (\Yii::$app->appParams->bans_view_files_count) {
            $select['files_count'] = $filesCountQuery;
            $sortAttributes['files_count'] = [
                'asc' => [
                    "({$filesCountQuery->createCommand()->getRawSql()})" => SORT_ASC,
                ],
                'desc' => [
                    "({$filesCountQuery->createCommand()->getRawSql()})" => SORT_DESC,
                ],
            ];
        }
        if (\Yii::$app->appParams->bans_view_comments_count) {
            $select['comments_count'] = $commentsCountQuery;
            $sortAttributes['comments_count'] = [
                'asc' => [
                    "({$commentsCountQuery->createCommand()->getRawSql()})" => SORT_ASC,
                ],
                'desc' => [
                    "({$commentsCountQuery->createCommand()->getRawSql()})" => SORT_DESC,
                ],
            ];
        }
        $query = Ban::find()->alias('ban')
            ->select($select)
            ->joinWith('server server');

        if (\Yii::$app->appParams->hide_old_bans) {
            $query->andWhere(self::ACTIVE_BAN_QUERY);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => \Yii::$app->appParams->bans_per_page ?: 50
            ],
            'sort' => [
                'defaultOrder' => ['ban_created' => SORT_DESC],
                'attributes' => $sortAttributes
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->query) {
            $query->andFilterWhere(['like', "ban.$this->field", $this->query]);
        }

        return $dataProvider;
    }

    public function fieldsForSearch(): array
    {
        $fields = [];
        foreach (self::SEARCH_FIELDS as $field) {
            $fields[$field] = $this->getAttributeLabel($field);
        }
        return $fields;
    }

    public function isPlayerBanned(string $ip): bool
    {
        return Ban::find()
            ->alias('ban')
            ->joinWith('server server')
            ->where(['ban.player_ip' => $ip])
            ->andWhere(self::ACTIVE_BAN_QUERY)
            ->exists();
    }
}
