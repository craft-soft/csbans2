<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\models\search;

use app\models\Log;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * search represents the model behind the search form of `app\models\Log`.
 */
class LogSearch extends Log
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ip', 'username', 'action', 'timestamp'], 'safe'],
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
        $query = Log::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['timestamp' => SORT_DESC]
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
            "FROM_UNIXTIME([[timestamp]], '%d.%m.%Y')" => $this->timestamp,
        ]);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'action', $this->action]);

        return $dataProvider;
    }

    public function actions(): array
    {
        $reflection = new \ReflectionClass($this);
        $actions = [];
        foreach ($reflection->getConstants() as $constant => $value) {
            if (str_starts_with($constant, 'ACTION')) {
                $actions[$constant] = Yii::t('admin/logs', $constant);
            }
        }
        sort($actions);
        return $actions;
    }
}
