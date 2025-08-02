<?php
// init_branding.php - create branding tables and seed data
require 'db.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS branding_briefs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conversacion_id INT NOT NULL,
  resumen_json JSON NOT NULL,
  confirmado TINYINT(1) DEFAULT 0,
  final_report LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (conversacion_id) REFERENCES conversaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS branding_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  texto VARCHAR(255) NOT NULL,
  orden INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS branding_intro (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mensaje TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$pdo->exec($sql);

$questions = [
    '¿Cuál es el nombre de la marca?',
    '¿Cuál es el eslogan?',
    '¿Cuál es la misión de la empresa?',
    '¿Cuál es la visión de la empresa?',
    '¿Cuál es el público objetivo principal?',
    '¿Cuáles son los valores de la marca?',
    '¿Qué productos o servicios ofrece?',
    '¿Qué diferencia a la marca de la competencia?',
    '¿Cuál es el tono de comunicación deseado?',
    '¿Qué colores representan a la marca?',
    '¿Qué tipografías se prefieren?',
    '¿Existe un logotipo actual?',
    '¿Cómo se describe la personalidad de la marca?',
    '¿Qué emociones quiere transmitir la marca?',
    '¿Qué medios de comunicación utiliza la marca?',
    '¿Cuál es el presupuesto estimado de marketing?',
    '¿Hay restricciones legales a considerar?',
    '¿Cuáles son los objetivos a corto plazo?',
    '¿Cuáles son los objetivos a largo plazo?',
    '¿Hay ejemplos de marcas que te inspiren?'
];

$count = $pdo->query('SELECT COUNT(*) FROM branding_questions')->fetchColumn();
if ($count == 0) {
    $stmt = $pdo->prepare('INSERT INTO branding_questions (texto, orden) VALUES (?, ?)');
    foreach ($questions as $i => $q) {
        $stmt->execute([$q, $i + 1]);
    }
}

$introExists = $pdo->query('SELECT COUNT(*) FROM branding_intro')->fetchColumn();
if ($introExists == 0) {
    $stmt = $pdo->prepare('INSERT INTO branding_intro (mensaje) VALUES (?)');
    $stmt->execute(['Bienvenido al cuestionario de branding. Responde las siguientes preguntas para crear tu brief.']);
}

$prompt = 'Eres un asistente de branding. Realiza 20 preguntas obligatorias para elaborar un brief. Cuando tengas suficientes datos responde con [[RESUMEN_COMPLETO]] seguido de un JSON con las respuestas. Tras recibir "confirmado" responde con [[CONFIRMADO]].';
$stmt = $pdo->prepare("SELECT COUNT(*) FROM prompt_lines WHERE role='system' AND content LIKE '%[[RESUMEN_COMPLETO]]%'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->prepare('INSERT INTO prompt_lines (set_id, role, content, orden) VALUES (1, "system", ?, 0)')->execute([$prompt]);
}

echo "Branding init complete\n";
