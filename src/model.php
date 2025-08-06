<?php
require_once 'db.php';

$dsn = 'mysql:host=localhost;dbname=app;charset=utf8mb4_0900_ai_ci';
$user = 'root';
$pass = 'root';
$db = new DB($dsn, $user, $pass);

class ItemModel extends DB
{
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
