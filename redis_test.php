<?php
$autoloader = require 'vendor/autoload.php';
$autoloader->addPsr4('', __DIR__);

// Запуск докер-контейнера:
// docker run --rm --name skill-redis -p 127.0.0.1:6379:6379/tcp -d redis

// Для теста будем считать неактивными пользователей, которые не заходили 2 секунды
const DELETE_SECONDS_AGO = 2;
// Допустим пользователи делают 500 запросов к сайту в секунду
const RPS = 500;
// И всего на сайт заходило 1000 различных пользователей
const USERS = 1000;

// Также мы добавим задержку между посещениями
const SLEEP = 900; // 900 микросекунд

$Redis = new RedisStorage();
$Redis->init();


// Эмулируем 10 секунд работы сайта
for ($seconds = 0; $seconds <= 10; $seconds++) {
    // Выполним 500 запросов
    for ($request = 0; $request < RPS; $request++) {
        $user_id = rand(1, USERS);
        $Redis->logPageVisit($user_id);
        usleep(SLEEP);
    }
    $Redis->deleteOldEntries(DELETE_SECONDS_AGO);
    $users_online = $Redis->calculateUsersNumber();
    $time = date("H:i:s");
    echo "[$time] Пользователей онлайн: $users_online \n";
}