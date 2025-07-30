<?php
// Simple helper function to query the OpenAI API using curl
function call_openai_api(array $messages): string {
    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        return 'Error: OPENAI_API_KEY not set';
    }

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if ($response === false) {
        return 'Error connecting to OpenAI API';
    }

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'No response';
}
