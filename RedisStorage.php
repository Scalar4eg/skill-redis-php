<?php
use Predis\Client;

class RedisStorage
{
    /**
     * @var Client
     */
    private $predis;
    const KEY = "ONLINE_USERS";

    // Подключается к Redis и удаляет ключ ONLINE_USERS, 
    // если он там уже есть
    public function init(): void
    {
        $this->predis = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
        $this->predis->connect();
        if (!$this->predis->isConnected()) {
            die("Не удалось подключиться к Redis");
        }
        $this->predis->del([self::KEY]);
    }

    // Фиксирует посещение пользователем страницы
    public function logPageVisit(int $user_id): void
    {
        //ZADD ONLINE_USERS
        $this->predis->zadd(self::KEY, [$user_id => time()]);
    }

    // Удаляет 
    public function deleteOldEntries(int $seconds_ago): void
    {
        //ZREVRANGEBYSCORE ONLINE_USERS 0 <time_5_seconds_ago>
        $this->predis->zremrangebyscore(self::KEY, 0, time()-$seconds_ago);
    }

    public function calculateUsersNumber(): int
    {
        //ZCOUNT ONLINE_USERS
        return (int)$this->predis->zcount(self::KEY, "-inf", "+inf");
    }
}