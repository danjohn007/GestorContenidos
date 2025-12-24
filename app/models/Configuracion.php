<?php
/**
 * Modelo Configuracion
 * Gestión de configuración del sistema
 */

class Configuracion {
    private $db;
    private $table = 'configuracion';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene un valor de configuración por clave
     */
    public function get($clave, $default = null) {
        $query = "SELECT valor FROM {$this->table} WHERE clave = :clave";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['clave' => $clave]);
        $result = $stmt->fetch();
        
        return $result ? $result['valor'] : $default;
    }

    /**
     * Obtiene múltiples valores de configuración por grupo
     */
    public function getByGrupo($grupo) {
        $query = "SELECT * FROM {$this->table} WHERE grupo = :grupo ORDER BY clave";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['grupo' => $grupo]);
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['clave']] = $row;
        }
        
        return $result;
    }

    /**
     * Obtiene todos los valores de configuración
     */
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY grupo, clave";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['clave']] = $row;
        }
        
        return $result;
    }

    /**
     * Actualiza un valor de configuración
     */
    public function set($clave, $valor) {
        $query = "UPDATE {$this->table} SET valor = :valor WHERE clave = :clave";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['clave' => $clave, 'valor' => $valor]);
    }

    /**
     * Crea o actualiza un valor de configuración
     */
    public function setOrCreate($clave, $valor, $tipo = 'texto', $grupo = 'general', $descripcion = null) {
        // Verificar si existe
        $existing = $this->get($clave);
        
        if ($existing !== null) {
            return $this->set($clave, $valor);
        }
        
        // Crear nuevo
        $query = "INSERT INTO {$this->table} (clave, valor, tipo, grupo, descripcion) 
                  VALUES (:clave, :valor, :tipo, :grupo, :descripcion)";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'clave' => $clave,
            'valor' => $valor,
            'tipo' => $tipo,
            'grupo' => $grupo,
            'descripcion' => $descripcion
        ]);
    }

    /**
     * Actualiza múltiples valores de configuración
     */
    public function setMultiple($valores) {
        $this->db->beginTransaction();
        
        try {
            foreach ($valores as $clave => $valor) {
                $this->set($clave, $valor);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Elimina un valor de configuración
     */
    public function delete($clave) {
        $query = "DELETE FROM {$this->table} WHERE clave = :clave";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['clave' => $clave]);
    }

    /**
     * Obtiene todos los grupos disponibles
     */
    public function getGrupos() {
        $query = "SELECT DISTINCT grupo FROM {$this->table} ORDER BY grupo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
