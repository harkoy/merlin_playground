-- Database schema for the chat application
-- Ensure you are using the marhar345_merlin database

CREATE DATABASE IF NOT EXISTS marhar345_merlin
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marhar345_merlin;

-- Prompt sets to allow different base instructions
CREATE TABLE IF NOT EXISTS prompt_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages belonging to each prompt set
CREATE TABLE IF NOT EXISTS prompt_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    set_id INT NOT NULL,
    role ENUM('system','assistant','user') NOT NULL,
    content TEXT NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (set_id) REFERENCES prompt_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table of users
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    apellido     VARCHAR(100) NOT NULL,
    empresa      VARCHAR(255),
    email        VARCHAR(255) NOT NULL UNIQUE,
    telefono     VARCHAR(50) UNIQUE,
    password     VARCHAR(255) NOT NULL,
    foto         VARCHAR(255),
    es_admin     TINYINT(1) DEFAULT 0,
    prompt_set_id INT DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prompt_set_id) REFERENCES prompt_sets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Design preferences
CREATE TABLE IF NOT EXISTS preferencias_disenio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tema ENUM('light','dark') DEFAULT 'light',
    color_preferido VARCHAR(50),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conversations
CREATE TABLE IF NOT EXISTS conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id INT NOT NULL,
    emisor ENUM('usuario','asistente') NOT NULL,
    texto TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversacion_id) REFERENCES conversaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin defined questions
CREATE TABLE IF NOT EXISTS preguntas_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto_pregunta TEXT NOT NULL,
    orden INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User answers to admin questions
CREATE TABLE IF NOT EXISTS respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta TEXT,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas_admin(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional analysis results
CREATE TABLE IF NOT EXISTS resultados_analisis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    analisis TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens for recovery process
CREATE TABLE IF NOT EXISTS password_resets (
    usuario_id INT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branding brief tables
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

-- Seed branding questions
INSERT INTO branding_questions (texto, orden) VALUES
('¿Cuál es el nombre de la marca?',1),
('¿Cuál es el eslogan?',2),
('¿Cuál es la misión de la empresa?',3),
('¿Cuál es la visión de la empresa?',4),
('¿Cuál es el público objetivo principal?',5),
('¿Cuáles son los valores de la marca?',6),
('¿Qué productos o servicios ofrece?',7),
('¿Qué diferencia a la marca de la competencia?',8),
('¿Cuál es el tono de comunicación deseado?',9),
('¿Qué colores representan a la marca?',10),
('¿Qué tipografías se prefieren?',11),
('¿Existe un logotipo actual?',12),
('¿Cómo se describe la personalidad de la marca?',13),
('¿Qué emociones quiere transmitir la marca?',14),
('¿Qué medios de comunicación utiliza la marca?',15),
('¿Cuál es el presupuesto estimado de marketing?',16),
('¿Hay restricciones legales a considerar?',17),
('¿Cuáles son los objetivos a corto plazo?',18),
('¿Cuáles son los objetivos a largo plazo?',19),
('¿Hay ejemplos de marcas que te inspiren?',20);

INSERT INTO branding_intro (mensaje) VALUES
('Bienvenido al cuestionario de branding. Responde las siguientes preguntas para crear tu brief.');

-- Ensure system prompt exists
INSERT INTO prompt_lines (set_id, role, content, orden)
SELECT 1, 'system', 'Eres un asistente de branding. Realiza 20 preguntas obligatorias para elaborar un brief. Cuando tengas suficientes datos responde con [[RESUMEN_COMPLETO]] seguido de un JSON con las respuestas. Tras recibir "confirmado" responde con [[CONFIRMADO]].', 0
WHERE NOT EXISTS (
    SELECT 1 FROM prompt_lines WHERE role = 'system' AND content LIKE '%[[RESUMEN_COMPLETO]]%'
);
