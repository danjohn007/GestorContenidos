<?php
/**
 * Modelo Log
 * Gestión de logs del sistema
 */

class Log {
    private $db;
    private $tableAcceso = 'logs_acceso';
    private $tableAuditoria = 'logs_auditoria';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene logs de acceso con paginación
     */
    public function getLogsAcceso($usuarioId = null, $exitoso = null, $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT la.*, u.nombre, u.apellidos
                  FROM {$this->tableAcceso} la
                  LEFT JOIN usuarios u ON la.usuario_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($usuarioId !== null) {
            $query .= " AND la.usuario_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($exitoso !== null) {
            $query .= " AND la.exitoso = :exitoso";
            $params['exitoso'] = $exitoso;
        }
        
        $query .= " ORDER BY la.fecha DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cuenta logs de acceso
     */
    public function countLogsAcceso($usuarioId = null, $exitoso = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->tableAcceso} WHERE 1=1";
        $params = [];
        
        if ($usuarioId !== null) {
            $query .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($exitoso !== null) {
            $query .= " AND exitoso = :exitoso";
            $params['exitoso'] = $exitoso;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }

    /**
     * Obtiene logs de auditoría con paginación
     */
    public function getLogsAuditoria($usuarioId = null, $modulo = null, $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT la.*, u.nombre, u.apellidos
                  FROM {$this->tableAuditoria} la
                  INNER JOIN usuarios u ON la.usuario_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($usuarioId !== null) {
            $query .= " AND la.usuario_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($modulo !== null) {
            $query .= " AND la.modulo = :modulo";
            $params['modulo'] = $modulo;
        }
        
        $query .= " ORDER BY la.fecha DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cuenta logs de auditoría
     */
    public function countLogsAuditoria($usuarioId = null, $modulo = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->tableAuditoria} WHERE 1=1";
        $params = [];
        
        if ($usuarioId !== null) {
            $query .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($modulo !== null) {
            $query .= " AND modulo = :modulo";
            $params['modulo'] = $modulo;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }

    /**
     * Registra un log de auditoría
     */
    public function registrarAuditoria($usuarioId, $modulo, $accion, $tabla = null, $registroId = null, $datosAnteriores = null, $datosNuevos = null) {
        $query = "INSERT INTO {$this->tableAuditoria} 
                  (usuario_id, modulo, accion, tabla, registro_id, datos_anteriores, datos_nuevos, ip) 
                  VALUES (:usuario_id, :modulo, :accion, :tabla, :registro_id, :datos_anteriores, :datos_nuevos, :ip)";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'accion' => $accion,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'datos_anteriores' => $datosAnteriores ? json_encode($datosAnteriores) : null,
            'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Obtiene módulos únicos de auditoría
     */
    public function getModulos() {
        $query = "SELECT DISTINCT modulo FROM {$this->tableAuditoria} ORDER BY modulo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
