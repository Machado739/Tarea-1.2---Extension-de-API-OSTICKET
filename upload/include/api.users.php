<?php

include_once INCLUDE_DIR . 'class.api.php';
include_once INCLUDE_DIR . 'class.user.php';

class UsersApiController extends ApiController {

    public function create($format) {
        // Requerir clave API
        $key = $this->requireApiKey();
    
        if (!$key) {
            return $this->respondWithError(401, 'API key no autorizada');
        }
    
        // Verificar y procesar el formato de solicitud
        if (!strcasecmp($format, 'email')) {
            return $this->respondWithError(500, 'Email no soportado en este momento');
        }
    
        // Parsear el cuerpo de la solicitud
        $data = $this->getRequest($format);
    
        // Validar los datos
        $validationErrors = $this->validateUserData($data);
        if ($validationErrors) {
            return $this->respondWithError(400, implode(', ', $validationErrors));
        }
    
        // Verificar si ya existe un usuario con el mismo email
        if (User::lookupByEmail($data['email'])) {
            return $this->respondWithError(409, 'Ya existe un usuario con este email');
        }
    
        // Crear el usuario
        $user = $this->createUser($data);
    
        if ($user) {
            return $this->respondWithSuccess([
                'message' => 'Usuario creado exitosamente',
                'user_id' => $user->getId(),
                'email' => $user->getEmail()->getAddress() // Ajuste para mostrar solo el correo electrónico
            ]);
        } else {
            return $this->respondWithError(500, 'No fue posible crear el usuario.');
        }
    }
    

    /**
     * Valida los datos del usuario.
     *
     * @param array $data Datos del usuario.
     * @return array Lista de errores de validación.
     */
    private function validateUserData($data) {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido o faltante';
        }
        if (empty($data['full_name'])) {
            $errors[] = 'Nombre completo requerido';
        }
        if (!empty($data['password']) && ($data['password'] !== ($data['confirm_password'] ?? ''))) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        return $errors;
    }

    /**
     * Crea un usuario con los datos proporcionados.
     *
     * @param array $data Datos del usuario.
     * @return User|null Usuario creado o null si falla.
     */
    private function createUser($data) {
        $user_data = array(
            'email' => $data['email'],
            'name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'passwd' => $data['password'] ?? null,
        );

        // Crear instancia de usuario
        $user = User::fromVars($user_data);

        // Intentar registrar la cuenta del usuario
        $errors = [];
        if (UserAccount::register($user, array(
            'sendemail' => false,
            'passwd1' => $data['password'],
            'timezone' => $data['timezone']
        ), $errors)) {
            // Usuario registrado correctamente
            return $user;
        } else {
            // Manejar errores de registro
            return null;
        }
    }

    /**
     * Responde con un mensaje de error en formato JSON.
     *
     * @param int $code Código de error HTTP.
     * @param string $message Mensaje de error.
     * @return string JSON con el mensaje de error.
     */
    private function respondWithError($code, $message) {
        http_response_code($code);
        return json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }

    /**
     * Responde con un mensaje de éxito en formato JSON.
     *
     * @param array $data Datos de la respuesta.
     * @return string JSON con el mensaje de éxito.
     */
    private function respondWithSuccess($data) {
        http_response_code(200);
        return json_encode(array_merge(['status' => 'success'], $data));
    }
}
