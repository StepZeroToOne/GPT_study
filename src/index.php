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
const SYSTEM_PROMPT = 'あなたはTRPGのゲームマスターです。シナリオはプレイヤーの指定がない限りランダムに作成し、シナリオを進めてください。プレイヤーがアイテムを入手したとき、そのアイテム名を「△△(プレイヤー名)は◯◯を手に入れた！」の形式で回答してください。';
session_start();
if (isset($_POST['reset'])) {
    $_SESSION['messages'] = [
        ['role' => 'system', 'content' => SYSTEM_PROMPT]
    ];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        ['role' => 'system', 'content' => SYSTEM_PROMPT]
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
    <title>TRPGセッション</title>
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
    <h1 style="font-family: 'Cinzel', serif; letter-spacing: 2px; color: #3a2e1a;">TRPGセッション</h1>

    <div style="background: #f4ecd8; border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px #c2b280;">
        <h2 style="margin-top:0; color: #6b4f1d;">ゲームマスター</h2>
        <?php if (!empty($_SESSION['messages'])): ?>
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                <?php foreach ($_SESSION['messages'] as $msg): ?>
                    <?php if ($msg['role'] === 'user'): ?>
                        <div style="margin: .5rem 0; padding: .5rem 1rem; background: #e8e0c9; border-radius: 6px;">
                            <strong>プレイヤー：</strong><?= htmlspecialchars($msg['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                        </div>
                    <?php elseif ($msg['role'] === 'assistant'): ?>
                        <div style="margin: .5rem 0; padding: .5rem 1rem; background: #fffbe6; border-radius: 6px;">
                            <strong>GM：</strong><?= htmlspecialchars($msg['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" style="background: #f4ecd8; border-radius: 10px; padding: 1rem; box-shadow: 0 2px 8px #c2b280;">
        <label for="prompt" style="font-weight:bold; color:#6b4f1d;">行動・発言を入力してください:</label>
        <textarea id="prompt" name="prompt" style="width:100%;height:100px;margin-top:.5rem;resize:vertical;"><?= htmlspecialchars($_POST['prompt'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></textarea>
        <br>
        <button type="submit" style="margin-top:1rem;padding:.5rem 1.5rem;font-size:1.1rem;background:#6b4f1d;color:#fff;border:none;border-radius:6px;cursor:pointer;">行動する</button>
    </form>
    <form method="post" style="margin-top:1rem;">
        <button type="submit" name="reset" value="1" style="background:#c00;color:#fff;padding:.5rem 1.5rem;border:none;border-radius:6px;cursor:pointer;">
            セッションをリセット
        </button>
    </form>
    <?php if ($error): ?>
        <p style="color:red; margin-top:1rem;"><?= htmlspecialchars($error, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

</body>

</html>