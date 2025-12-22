<?php
/**
 * Modelo RedesSociales
 * Gestión de enlaces a redes sociales
 */

class RedesSociales {
    private $db;
    private $table = 'redes_sociales';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las redes sociales activas
     */
    public function getAll($activo = true) {
        $query = "SELECT * FROM {$this->table}";
        
        if ($activo) {
            $query .= " WHERE activo = 1";
        }
        
        $query .= " ORDER BY orden ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una red social por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva red social
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, icono, url, orden, activo) 
                  VALUES (:nombre, :icono, :url, :orden, :activo)";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'nombre' => $data['nombre'],
            'icono' => $data['icono'],
            'url' => $data['url'],
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1
        ]);
    }

    /**
     * Actualiza una red social
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['nombre', 'icono', 'url', 'orden', 'activo'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }

    /**
     * Elimina una red social
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
}
