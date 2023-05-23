<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\AmxAdmin;

/**
 * AmxAdminsSearch represents the model behind the search form of `app\modules\admin\models\AmxAdmin`.
 */
class AmxAdminsSearch extends AmxAdmin
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'icq', 'ashow', 'created', 'expired', 'days'], 'integer'],
            [['username', 'password', 'access', 'flags', 'steamid', 'nickname'], 'safe'],
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
        $query = AmxAdmin::find();

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
            'id' => $this->id,
            'icq' => $this->icq,
            'ashow' => $this->ashow,
            "FROM_UNIXTIME(created, '%d.%m.%Y')" => $this->created,
            'expired' => $this->expired,
            'days' => $this->days,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'access', $this->access])
            ->andFilterWhere(['like', 'flags', $this->flags])
            ->andFilterWhere(['like', 'steamid', $this->steamid])
            ->andFilterWhere(['like', 'nickname', $this->nickname]);

        return $dataProvider;
    }
}
