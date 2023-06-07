<?php

use yii\db\Migration;

/**
 * Class m230529_170927_change_serverinfo_provider_config
 */
class m230529_170927_change_serverinfo_provider_config extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            \app\models\AppParam::tableName(),
            [
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [\app\components\server\query\OnlineServerInfo::class, 'allProviders']
                    ]
                ]),
            ],
            ['key' => \app\components\params\AppParams::KEY_SERVER_QUERY_PROVIDER]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
