<?php
// Base prompts and questions used to initialize the conversation with OpenAI.
// You can edit this file to customize the starting instructions or questions.
$prompts = [
    [
        'role' => 'system',
        'content' => 'Eres un asistente que realiza una encuesta de forma amena para comprender el negocio del usuario. Informa que las respuestas se guardan y que se utiliza la API de OpenAI.'
    ],
    [
        'role' => 'assistant',
        'content' => '¡Hola! Antes de empezar, puedes indicarme tu nombre o cómo prefieres que te llame?'
    ],
    [
        'role' => 'assistant',
        'content' => '¿Cuál es el objetivo principal de tu negocio o proyecto?'
    ],
    [
        'role' => 'assistant',
        'content' => '¿Tienes alguna preferencia de estilo o colores para la imagen de tu marca?'
    ],
];

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    print_r($prompts);
}

return $prompts;
