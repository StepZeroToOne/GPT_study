<?php

/**
 * ChatGPT API 簡易フロントエンド
 * --------------------------------
 * 1. 画面下部のフォームにテキストを入力して送信
 * 2. OpenAI Chat Completions API を呼び出しレスポンスを表示
 * 
 * 必要環境変数:
 *   OPENAI_API_KEY  ... OpenAI のシークレットキー
 */
session_start();
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        ['role' => 'system', 'content' => 'あなたはデスゲームのマスターです。プレイヤーがアイテムを入手したとき、そのアイテム名を「△△(プレイヤー名)は◯◯を手に入れた！」の形式で回答してください。']
    ];
}
$answer = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = trim($_POST['prompt'] ?? '');

    if ($prompt === '') {
        $answer = 'テキストを入力してください。';
    } elseif (! getenv('OPENAI_API_KEY')) {
        $error = '環境変数 OPENAI_API_KEY が設定されていません。';
    } else {
        $apiKey = getenv('OPENAI_API_KEY');
        $_SESSION['messages'][] = ['role' => 'user', 'content' => $prompt];
        // Chat Completions API のリクエストペイロード
        $payload = [
            'model'       => 'gpt-4o-mini',       // 必要に応じて変更
            'messages'    => $_SESSION['messages'],
            'temperature' => 0.7
        ];

        // cURL で API コール
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}"
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = 'cURL エラー: ' . curl_error($ch);
        } else {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($status !== 200) {
                $error = "API から HTTP {$status} が返されました。";
            } else {
                $data   = json_decode($response, true);
                $answer = $data['choices'][0]['message']['content'] ?? '(回答なし)';
            }
        }
        if (isset($answer)) {
            $_SESSION['messages'][] = ['role' => 'assistant', 'content' => $answer];
        }
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ChatGPT API テスト</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 2rem;
        }

        textarea {
            width: 100%;
            height: 130px;
            margin-top: .5rem;
        }

        button {
            margin-top: 1rem;
            padding: .5rem 1rem;
            font-size: 1rem;
        }

        pre {
            background: #f7f7f7;
            padding: 1rem;
            border-radius: 6px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <h1>ChatGPT API テスト</h1>

    <form method="post">
        <label for="prompt">質問を入力してください:</label>
        <textarea id="prompt" name="prompt"><?= htmlspecialchars($_POST['prompt'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></textarea>
        <br>
        <button type="submit">送信</button>
    </form>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
    <?php elseif ($answer): ?>
        <h2>回答</h2>
        <pre><?= htmlspecialchars($answer, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></pre>
    <?php endif; ?>

</body>

</html>