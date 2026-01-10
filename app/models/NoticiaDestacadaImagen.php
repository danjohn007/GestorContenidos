<?php
/**
 * Modelo NoticiaDestacadaImagen
 * Gestión de noticias destacadas visuales (solo imágenes)
 */

class NoticiaDestacadaImagen {
    private $db;
    private $table = 'noticias_destacadas_imagenes';

    // Ubicaciones disponibles
    const UBICACION_BAJO_SLIDER = 'bajo_slider';
    const UBICACION_ENTRE_BLOQUES = 'entre_bloques';
    const UBICACION_ANTES_FOOTER = 'antes_footer';

    // Tipos de vista
    const VISTA_GRID = 'grid';
    const VISTA_CAROUSEL = 'carousel';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las ubicaciones disponibles
     */
    public static function getUbicaciones() {
        return [
            self::UBICACION_BAJO_SLIDER => 'Bajo el slider principal',
            self::UBICACION_ENTRE_BLOQUES => 'Entre bloques de inicio',
            self::UBICACION_ANTES_FOOTER => 'Antes del footer'
        ];
    }

    /**
     * Obtiene todos los tipos de vista
     */
    public static function getVistas() {
        return [
            self::VISTA_GRID => 'Grid (cuadrícula)',
            self::VISTA_CAROUSEL => 'Carrusel'
        ];
    }

    /**
     * Obtiene todas las noticias destacadas
     */
    public function getAll($activo = null, $ubicacion = null) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($activo !== null) {
            $query .= " AND activo = :activo";
            $params['activo'] = $activo;
        }

        if ($ubicacion !== null) {
            $query .= " AND ubicacion = :ubicacion";
            $params['ubicacion'] = $ubicacion;
        }

        // Filtrar por fechas de vigencia
        $query .= " AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())";
        $query .= " AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())";
        
        $query .= " ORDER BY orden ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene noticias destacadas por ubicación
     */
    public function getByUbicacion($ubicacion) {
        return $this->getAll(1, $ubicacion);
    }

    /**
     * Obtiene una noticia destacada por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva noticia destacada
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (titulo, imagen_url, url_destino, noticia_id, ubicacion, vista, orden, activo, 
                   fecha_inicio, fecha_fin) 
                  VALUES (:titulo, :imagen_url, :url_destino, :noticia_id, :ubicacion, :vista, 
                          :orden, :activo, :fecha_inicio, :fecha_fin)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'titulo' => $data['titulo'],
            'imagen_url' => $data['imagen_url'],
            'url_destino' => $data['url_destino'] ?? null,
            'noticia_id' => $data['noticia_id'] ?? null,
            'ubicacion' => $data['ubicacion'] ?? self::UBICACION_BAJO_SLIDER,
            'vista' => $data['vista'] ?? self::VISTA_GRID,
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1,
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza una noticia destacada
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['titulo', 'imagen_url', 'url_destino', 'noticia_id', 'ubicacion', 
                          'vista', 'orden', 'activo', 'fecha_inicio', 'fecha_fin'];
        
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
     * Elimina una noticia destacada
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cambia el estado activo/inactivo
     */
    public function toggle($id) {
        $noticia = $this->getById($id);
        if (!$noticia) {
            return false;
        }
        
        $nuevoEstado = $noticia['activo'] ? 0 : 1;
        return $this->update($id, ['activo' => $nuevoEstado]);
    }

    /**
     * Actualiza el orden de múltiples noticias destacadas
     */
    public function updateOrden($ordenes) {
        $query = "UPDATE {$this->table} SET orden = :orden WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        foreach ($ordenes as $id => $orden) {
            $stmt->execute(['id' => $id, 'orden' => $orden]);
        }
        
        return true;
    }
}
?>
