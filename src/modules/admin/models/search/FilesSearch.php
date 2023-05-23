<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use yii\data\ActiveDataProvider;
use app\modules\admin\models\File;

/**
 * CommentsSearch represents the model behind the search form of `app\modules\admin\models\Comment`.
 */
class FilesSearch extends File
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'moderated'], 'integer'],
            [['name', 'comment', 'email', 'addr', 'bid', 'demo_file', 'upload_time'], 'safe'],
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
        $query = File::find()->alias('file')->joinWith(['ban ban', 'ban.server server'])->where(['file.moderated' =>
            0]);

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
            'file.id' => $this->id,
            "FROM_UNIXTIME(file.upload_time, '%d.%m.%Y')" => $this->upload_time,
        ]);

        $query->andFilterWhere(['like', 'file.name', $this->name])
            ->andFilterWhere(['like', 'file.comment', $this->comment])
            ->andFilterWhere(['like', 'file.email', $this->email])
            ->andFilterWhere(['like', 'file.demo_file', $this->demo_file])
            ->andFilterWhere(['like', 'ban.player_nick', $this->bid])
            ->andFilterWhere(['like', 'file.addr', $this->addr]);

        return $dataProvider;
    }
}
