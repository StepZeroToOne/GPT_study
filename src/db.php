<?php

/**
 * ChatGPT 参照データベース
 * --------------------------------
 * 1. GPTの回答からユーザー、アイテム取得,保存
 * 2. DBに基づきプロンプトを生成
 */
class DB
{
    private $pdo;

    public function __construct($dsn, $user, $pass)
    {
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    // 登録系（INSERT）
    public function executeInsert($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // 更新系（UPDATE）
    public function executeUpdate($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // 取得系（SELECT）
    public function executeSelect($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
