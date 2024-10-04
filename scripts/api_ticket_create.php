#!/usr/bin/php -q
<?php

// Configuración: Utilizando variables de entorno para evitar exponer datos sensibles en el código
$config = array(
    'url' => getenv('API_URL'),
    'key' => getenv('API_KEY')
);

// Verificación de la configuración
if (!$config['url'] || !$config['key']) {
    die("Error: Configuración de URL o clave API no válida.\n");
}

// Recolectar datos para el nuevo ticket (idealmente estos vendrían desde $_POST)
$data = array(
    'name'      => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?: 'John Doe',
    'email'     => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: 'mailbox@host.com',
    'subject'   => filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING) ?: 'Test API message',
    'message'   => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?: 'This is a test of the osTicket API',
    'ip'        => $_SERVER['REMOTE_ADDR'],
    'attachments' => array(),
);

// Añadir adjuntos si es necesario (a partir de archivos subidos, por ejemplo)
if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['tmp_name'])) {
    foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $data['attachments'][] = array(
                $_FILES['attachments']['name'][$index] => 
                'data:' . mime_content_type($tmpName) . ';base64,' . base64_encode(file_get_contents($tmpName))
            );
        }
    }
}

// Verificación previa: Asegurarse de que cURL y JSON estén disponibles
if (!function_exists('curl_version')) {
    die('Error: Se requiere soporte de CURL.\n');
}
if (!function_exists('json_encode')) {
    die('Error: Se requiere soporte de JSON.\n');
}

// Configuración de tiempo de espera
set_time_limit(30);

// Configuración de cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['url']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.7');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Expect:', 
    'X-API-Key: ' . $config['key'],
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Ejecutar cURL y manejar errores
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode != 201) {
    die('Error al crear el ticket: ' . ($curlError ? $curlError : $result) . "\n");
}

// Obtener ID del ticket creado
$ticket = json_decode($result, true);
$ticket_id = isset($ticket['id']) ? (int) $ticket['id'] : null;

if (!$ticket_id) {
    die("Error: No se pudo obtener el ID del ticket.\n");
}

// Continuar con el proceso si es necesario
echo "Ticket creado exitosamente con ID: " . $ticket_id . "\n";

?>
