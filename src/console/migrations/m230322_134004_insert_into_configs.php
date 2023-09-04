<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\components\params\AppParams;
use app\models\AppParam;
use yii\db\Migration;

/**
 * Class m230322_134004_insert_into_configs
 */
class m230322_134004_insert_into_configs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();

        $rows = [
            [
                'label' => 'SITE_THEME',
                'type' => AppParam::TYPE_DROPDOWN,
                'block' => AppParams::BLOCK_VIEW,
                'key' => AppParams::KEY_VIEW_SITE_THEME,
                'value_string' => 'default',
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [\app\components\theme\Factory::class, 'getList'],
                        'params' => [true]
                    ]
                ]),
                'sort' => 90,
                'updated_at' => $time,
            ],
            [
                'label' => 'START_PAGE',
                'type' => AppParam::TYPE_DROPDOWN,
                'block' => AppParams::BLOCK_VIEW,
                'key' => AppParams::KEY_VIEW_START_PAGE,
                'value_string' => '/',
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [\app\models\Link::class, 'getList'],
                    ]
                ]),
                'sort' => 80,
                'updated_at' => $time,
            ],
            [
                'label' => 'DEMO_UPLOAD_ENABLED',
                'type' => AppParam::TYPE_DROPDOWN,
                'block' => AppParams::BLOCK_FILES,
                'key' => AppParams::KEY_FILES_DEMO_UPLOAD_ENABLED,
                'value_string' => AppParams::VALUE_ALL,
                'dropdown_options' => json_encode(AppParams::VALUES),
                'sort' => 90,
                'updated_at' => $time,
            ],
            [
                'label' => 'DEMO_FILE_TYPES',
                'type' => AppParam::TYPE_STRING,
                'block' => AppParams::BLOCK_FILES,
                'key' => AppParams::KEY_FILES_DEMO_FILE_TYPES,
                'value_string' => 'dem,zip,rar,jpg,gif,png',
                'sort' => 80,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_PER_PAGE',
                'type' => AppParam::TYPE_INTEGER,
                'block' => AppParams::BLOCK_BANS,
                'key' => AppParams::KEY_BANS_PER_PAGE,
                'value_int' => 50,
                'sort' => 100,
                'updated_at' => $time,
            ],

            [
                'label' => 'SITE_BAN_PERIOD',
                'type' => AppParam::TYPE_INTEGER,
                'key' => AppParams::KEY_BANS_SITE_BAN_PERIOD,
                'block' => AppParams::BLOCK_BANS,
                'value_int' => 2,
                'sort' => 90,
                'updated_at' => $time,
            ],
            [
                'label' => 'SITE_BAN_REASON',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_BANS_SITE_BAN_REASON,
                'block' => AppParams::BLOCK_BANS,
                'value_string' => 'csbans',
                'sort' => 80,
                'updated_at' => $time,
            ],
            [
                'label' => 'MAX_OFFENCES',
                'type' => AppParam::TYPE_INTEGER,
                'key' => AppParams::KEY_BANS_MAX_OFFENCES,
                'block' => AppParams::BLOCK_BANS,
                'value_int' => 10,
                'sort' => 70,
                'updated_at' => $time,
            ],
            [
                'label' => 'MAX_OFFENCES_REASON',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_BANS_MAX_OFFENCES_REASON,
                'block' => AppParams::BLOCK_BANS,
                'value_string' => 'max offences reached',
                'sort' => 60,
                'updated_at' => $time,
            ],
            [
                'label' => 'HIDE_OLD_BANS',
                'type' => AppParam::TYPE_BOOLEAN,
                'key' => AppParams::KEY_BANS_HIDE_OLD_BANS,
                'block' => AppParams::BLOCK_BANS,
                'value_bool' => 0,
                'sort' => 35,
                'updated_at' => $time,
            ],
            [
                'label' => 'COMMENTS_ENABLED',
                'type' => AppParam::TYPE_DROPDOWN,
                'key' => AppParams::KEY_BANS_COMMENTS,
                'block' => AppParams::BLOCK_BANS,
                'value_string' => AppParams::VALUE_ALL,
                'dropdown_options' => json_encode(AppParams::VALUES),
                'sort' => 40,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_VIEW_COMMENTS_COUNT',
                'type' => AppParam::TYPE_BOOLEAN,
                'block' => AppParams::BLOCK_BANS,
                'key' => AppParams::KEY_BANS_VIEW_COMMENTS_COUNT,
                'value_bool' => 1,
                'sort' => 30,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_VIEW_FILES_COUNT',
                'type' => AppParam::TYPE_BOOLEAN,
                'block' => AppParams::BLOCK_BANS,
                'key' => AppParams::KEY_BANS_VIEW_FILES_COUNT,
                'value_bool' => 1,
                'sort' => 20,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_VIEW_KICKS_COUNT',
                'type' => AppParam::TYPE_BOOLEAN,
                'block' => AppParams::BLOCK_BANS,
                'key' => AppParams::KEY_BANS_VIEW_KICKS_COUNT,
                'value_bool' => 1,
                'sort' => 10,
                'updated_at' => $time,
            ],
            [
                'label' => 'MAIN_SITE_NAME',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_MAIN_SITE_NAME,
                'value_string' => 'CSBans 2',
                'sort' => 100,
                'updated_at' => $time,
            ],
            [
                'label' => 'MAIN_SITE_BASE_URL',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_MAIN_SITE_BASE_URL,
                'sort' => 90,
                'updated_at' => $time,
            ],
            [
                'label' => 'IP_DATA_PROVIDER',
                'type' => AppParam::TYPE_DROPDOWN,
                'key' => AppParams::KEY_IP_DATA_PROVIDER,
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [\app\components\ipGeo\IpGeo::class, 'allProviders']
                    ]
                ]),
                'value_string' => 1,
                'sort' => 80,
                'updated_at' => $time,
            ],
            [
                'label' => 'IP_VIEW_PROVIDER_CRED',
                'type' => AppParam::TYPE_BOOLEAN,
                'key' => AppParams::KEY_IP_VIEW_PROVIDER_CRED,
                'value_bool' => 1,
                'sort' => 79,
                'updated_at' => $time,
            ],
            [
                'label' => 'SERVER_QUERY_PROVIDER',
                'type' => AppParam::TYPE_DROPDOWN,
                'key' => AppParams::KEY_SERVER_QUERY_PROVIDER,
                'block' => AppParams::BLOCK_SERVER,
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [OnlineServerInfo::class, 'allProviders']
                    ]
                ]),
                'value_string' => 1,
                'sort' => 100,
                'updated_at' => $time,
            ],
            [
                'label' => 'EXTERNAL_YANDEX_MAPS_LABEL',
                'type' => AppParam::TYPE_LABEL,
                'key' => 'external_yandex_label',
                'block' => AppParams::BLOCK_EXTERNAL,
                'sort' => 100,
                'updated_at' => $time,
            ],
            [
                'label' => 'EXTERNAL_YANDEX_MAPS_ENABLED',
                'type' => AppParam::TYPE_BOOLEAN,
                'key' => AppParams::KEY_EXTERNAL_YANDEX_MAPS_ENABLED,
                'block' => AppParams::BLOCK_EXTERNAL,
                'value_bool' => 1,
                'sort' => 90,
                'updated_at' => $time,
            ],
            [
                'label' => 'EXTERNAL_YANDEX_API_KEY',
                'type' => AppParam::TYPE_PASSWORD,
                'key' => AppParams::KEY_EXTERNAL_YANDEX_API_KEY,
                'block' => AppParams::BLOCK_EXTERNAL,
                'description' => 'EXTERNAL_YANDEX_API_KEY_DESCRIPTION',
                'sort' => 80,
                'updated_at' => $time,
            ],
            [
                'label' => 'SERVERS_DATA_INTERVAL',
                'type' => AppParam::TYPE_INTEGER,
                'key' => AppParams::KEY_SERVERS_DATA_INTERVAL,
                'block' => AppParams::BLOCK_SERVER,
                'value_int' => 3,
                'description' => 'SERVERS_DATA_INTERVAL_DESCRIPTION',
                'sort' => 90,
                'to_frontend' => 1,
                'updated_at' => $time,
            ],
            [
                'label' => 'MAIN_SITE_LANGUAGE',
                'type' => AppParam::TYPE_DROPDOWN,
                'key' => AppParams::KEY_MAIN_SITE_LANGUAGE,
                'dropdown_options' => json_encode([
                    'callable' => [
                        'method' => [AppParams::class, 'languages'],
                    ]
                ]),
                'value_string' => 'ru',
                'sort' => 85,
                'updated_at' => $time,
            ],
            [
                'label' => 'VIEW_MAIN_PAGE_BANNER',
                'type' => AppParam::TYPE_TEXT,
                'key' => AppParams::KEY_VIEW_MAIN_BANNER,
                'block' => AppParams::BLOCK_VIEW,
                'value_text' => '<h1>CS:Bans 2</h1>',
                'sort' => 60,
                'updated_at' => $time,
            ],
            [
                'label' => 'VIEW_INDEX_PAGE_TOP',
                'type' => AppParam::TYPE_TEXT,
                'key' => AppParams::KEY_VIEW_VIEW_INDEX_PAGE_HTML,
                'block' => AppParams::BLOCK_VIEW,
                'value_text' => <<<HTML
<div class="p-5 mb-4 rounded-3 bg-dark">
  <div class="container-fluid py-5">
    <h1 class="display-5 fw-bold">{{ t('index', 'JUMBOTRON_TITLE') }}</h1>
    <p class="col-md-8 fs-4">{{ t('index', 'JUMBOTRON_TEXT') }}</p>
  </div>
</div>
HTML,
                'sort' => 59,
                'updated_at' => $time,
            ],
            [
                'label' => 'VIEW_FOOTER_LEFT',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_VIEW_FOOTER_LEFT,
                'block' => AppParams::BLOCK_VIEW,
                'value_string' => 'CS:Bans 2',
                'sort' => 50,
                'updated_at' => $time,
            ],
            [
                'label' => 'VIEW_FOOTER_RIGHT',
                'type' => AppParam::TYPE_STRING,
                'key' => AppParams::KEY_VIEW_FOOTER_RIGHT,
                'block' => AppParams::BLOCK_VIEW,
                'value_string' => '&copy; <a href="https://craft-soft.ru" target="_blank">Craft Soft team</a></strong>',
                'sort' => 49,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_MODERATE_COMMENTS',
                'type' => AppParam::TYPE_BOOLEAN,
                'key' => AppParams::KEY_BANS_MODERATE_COMMENTS,
                'block' => AppParams::BLOCK_BANS,
                'value_bool' => 0,
                'sort' => 38,
                'updated_at' => $time,
            ],
            [
                'label' => 'BANS_MODERATE_FILES',
                'type' => AppParam::TYPE_BOOLEAN,
                'key' => AppParams::KEY_BANS_MODERATE_FILES,
                'block' => AppParams::BLOCK_BANS,
                'value_bool' => 0,
                'sort' => 37,
                'updated_at' => $time,
            ]
        ];
        foreach ($rows as $row) {
            $this->insert(AppParam::tableName(), $row);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(AppParam::tableName());
    }
}
