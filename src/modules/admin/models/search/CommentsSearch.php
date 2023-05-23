<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Comment;

/**
 * CommentsSearch represents the model behind the search form of `app\modules\admin\models\Comment`.
 */
class CommentsSearch extends Comment
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'moderated'], 'integer'],
            [['name', 'comment', 'email', 'addr', 'bid', 'date'], 'safe'],
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
        $query = Comment::find()->alias('comment')->joinWith(['ban ban', 'ban.server server'])->where(['comment.moderated' => 0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
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
            'comment.id' => $this->id,
            "FROM_UNIXTIME(comment.date, '%d.%m.%Y')" => $this->date,
        ]);

        $query->andFilterWhere(['like', 'comment.name', $this->name])
            ->andFilterWhere(['like', 'comment.comment', $this->comment])
            ->andFilterWhere(['like', 'comment.email', $this->email])
            ->andFilterWhere(['like', 'ban.player_nick', $this->bid])
            ->andFilterWhere(['like', 'comment.addr', $this->addr]);

        return $dataProvider;
    }
}
