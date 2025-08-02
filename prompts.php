<?php
// prompts.php - Define prompt sets for OpenAI initialization.
// Each set is keyed by a name and contains an array of messages.
$promptSets = [
    'default' => [
        [
            'role' => 'system',
            'content' => 'Eres un asistente de investigación de marca. Conversa de forma amigable para recopilar información que ayude a definir la identidad de una marca y el diseño de su logotipo. Mantente enfocado en esta misión y deriva cordialmente cualquier tema ajeno al objetivo.'
        ],
        [
            'role' => 'assistant',
            'content' => 'Hola, estoy aquí para ayudarte a definir tu marca. Si lo prefieres, puedes responder un formulario directamente en lugar de conversar. ¿Qué opción eliges?'
        ],
    ],
];

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    print_r($promptSets);
}

return $promptSets;
?>
