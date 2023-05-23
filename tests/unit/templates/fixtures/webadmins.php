<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

/** @var \Faker\Generator $faker */

return [
    'username' => $faker->userName,
    'password' => md5($faker->password),
    'level' => 1,
    'email' => $faker->email,
    'last_action' => $faker->unixTime,
    'try' => $faker->randomElement([0, 1, 2, 3]),
];
