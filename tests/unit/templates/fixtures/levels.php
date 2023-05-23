<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

/** @var \Faker\Generator $faker */

$yesNo = ['yes', 'no'];
$yesNoJwn = ['yes', 'no', 'own'];

return [
    'level' => 1,
    'bans_add' => $faker->randomElement($yesNo),
    'bans_edit' => $faker->randomElement($yesNoJwn),
    'bans_delete' => $faker->randomElement($yesNoJwn),
    'bans_unban' => $faker->randomElement($yesNoJwn),
    'bans_import' => $faker->randomElement($yesNo),
    'bans_export' => $faker->randomElement($yesNo),
    'amxadmins_view' => $faker->randomElement($yesNo),
    'amxadmins_edit' => $faker->randomElement($yesNo),
    'webadmins_view' => $faker->randomElement($yesNo),
    'webadmins_edit' => $faker->randomElement($yesNo),
    'websettings_view' => $faker->randomElement($yesNo),
    'websettings_edit' => $faker->randomElement($yesNo),
    'permissions_edit' => $faker->randomElement($yesNo),
    'prune_db' => $faker->randomElement($yesNo),
    'servers_edit' => $faker->randomElement($yesNo),
    'ip_view' => $faker->randomElement($yesNo),
];
