<?php
require_once 'db.php';

$dsn = 'mysql:host=localhost;dbname=app;charset=utf8mb4_0900_ai_ci';
$user = 'root';
$pass = 'root';
$db = new DB($dsn, $user, $pass);

class Model extends DB
{
    // ユーザーを登録するメソッド
    public function insertUser($status, $userName)
    {
        $sql = "INSERT INTO users (user_name,hp,atk,def,item) VALUES (?, ?, ?, ?, ?)";
        return $this->executeInsert($sql, [$userName, $status['hp'], $status['atk'], $status['def'], $status['item']]);
    }
    // ユーザーをアップデートするメソッド
    public function updateUser($userId, $itemName)
    {
        $sql = "INSERT INTO items (user_id, item_name) VALUES (?, ?)";
        return $this->executeUpdate($sql, [$userId, $itemName]);
    }
    // ユーザー一覧を取得するメソッド
    public function getUsers($userId)
    {
        $sql = "SELECT * FROM items WHERE user_id = ?";
        return $this->executeSelect($sql, [$userId]);
    }

    // アイテムを保存するメソッド
    public function saveItem($userId, $itemName)
    {
        $sql = "INSERT INTO items (user_id, item_name) VALUES (?, ?)";
        return $this->executeInsert($sql, [$userId, $itemName]);
    }

    // ユーザーのアイテム一覧を取得するメソッド
    public function getItems($userId)
    {
        $sql = "SELECT * FROM items WHERE user_id = ?";
        return $this->executeSelect($sql, [$userId]);
    }
}
