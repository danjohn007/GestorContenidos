<?php
/**
 * Modelo Usuario
 * Gestión de usuarios del sistema
 */

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene un usuario por su email
     */
    public function getByEmail($email) {
        $query = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.rol_id = r.id 
                  WHERE u.email = :email";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Obtiene un usuario por su ID
     */
    public function getById($id) {
        $query = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.rol_id = r.id 
                  WHERE u.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene todos los usuarios
     */
    public function getAll($activo = null) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM {$this->table} u 
                  INNER JOIN roles r ON u.rol_id = r.id";
        
        if ($activo !== null) {
            $query .= " WHERE u.activo = :activo";
        }
        
        $query .= " ORDER BY u.nombre, u.apellidos";
        
        $stmt = $this->db->prepare($query);
        
        if ($activo !== null) {
            $stmt->execute(['activo' => $activo]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, apellidos, email, password, rol_id, activo, creado_por) 
                  VALUES (:nombre, :apellidos, :email, :password, :rol_id, :activo, :creado_por)";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'rol_id' => $data['rol_id'],
            'activo' => $data['activo'] ?? 1,
            'creado_por' => $data['creado_por'] ?? null
        ]);
    }

    /**
     * Actualiza un usuario
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params['nombre'] = $data['nombre'];
        }
        
        if (isset($data['apellidos'])) {
            $fields[] = "apellidos = :apellidos";
            $params['apellidos'] = $data['apellidos'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params['email'] = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['rol_id'])) {
            $fields[] = "rol_id = :rol_id";
            $params['rol_id'] = $data['rol_id'];
        }
        
        if (isset($data['activo'])) {
            $fields[] = "activo = :activo";
            $params['activo'] = $data['activo'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }

    /**
     * Elimina (baja lógica) un usuario
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verifica credenciales y retorna el usuario si son correctas
     */
    public function login($email, $password) {
        $usuario = $this->getByEmail($email);
        
        if (!$usuario) {
            return false;
        }
        
        // Verificar si el usuario está bloqueado
        if (!$usuario['activo']) {
            return false;
        }
        
        // Verificar si hay demasiados intentos fallidos
        if ($usuario['intentos_fallidos'] >= MAX_LOGIN_ATTEMPTS) {
            return false;
        }
        
        // Verificar contraseña
        if (password_verify($password, $usuario['password'])) {
            // Reset intentos fallidos y actualizar último acceso
            $this->resetLoginAttempts($usuario['id']);
            $this->updateLastAccess($usuario['id']);
            return $usuario;
        } else {
            // Incrementar intentos fallidos
            $this->incrementLoginAttempts($usuario['id']);
            return false;
        }
    }

    /**
     * Incrementa los intentos fallidos de login
     */
    private function incrementLoginAttempts($userId) {
        $query = "UPDATE {$this->table} 
                  SET intentos_fallidos = intentos_fallidos + 1 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $userId]);
    }

    /**
     * Resetea los intentos fallidos de login
     */
    private function resetLoginAttempts($userId) {
        $query = "UPDATE {$this->table} 
                  SET intentos_fallidos = 0 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $userId]);
    }

    /**
     * Actualiza el último acceso del usuario
     */
    private function updateLastAccess($userId) {
        $query = "UPDATE {$this->table} 
                  SET ultimo_acceso = NOW() 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $userId]);
    }

    /**
     * Registra un acceso al sistema
     */
    public function logAccess($usuarioId, $email, $accion, $exitoso, $mensaje = null) {
        $query = "INSERT INTO logs_acceso 
                  (usuario_id, email, accion, ip, user_agent, exitoso, mensaje) 
                  VALUES (:usuario_id, :email, :accion, :ip, :user_agent, :exitoso, :mensaje)";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'email' => $email,
            'accion' => $accion,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'exitoso' => $exitoso,
            'mensaje' => $mensaje
        ]);
    }

    /**
     * Obtiene el historial de actividad de un usuario
     */
    public function getActivityHistory($usuarioId, $limit = 50) {
        $query = "SELECT * FROM logs_acceso 
                  WHERE usuario_id = :usuario_id 
                  ORDER BY fecha DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
