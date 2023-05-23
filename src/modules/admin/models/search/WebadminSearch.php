<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use yii\data\ActiveDataProvider;
use app\modules\admin\models\Webadmin;

/**
 * WebadminSearch represents the model behind the search form of `app\modules\admin\models\Webadmin`.
 */
class WebadminSearch extends Webadmin
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'level', 'last_action', 'try'], 'integer'],
            [['username', 'password', 'logcode', 'email'], 'safe'],
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
        $query = Webadmin::find();

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
            "FROM_UNIXTIME(last_action, '%d.%m.%Y')" => $this->last_action,
            'try' => $this->try,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'logcode', $this->logcode])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
