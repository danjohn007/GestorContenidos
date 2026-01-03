<?php
/**
 * Modelo Banner
 * Gestión de banners publicitarios
 */

class Banner {
    private $db;
    private $table = 'banners';

    // Ubicaciones disponibles para los banners
    const UBICACION_INICIO = 'inicio';
    const UBICACION_SIDEBAR = 'sidebar';
    const UBICACION_FOOTER = 'footer';
    const UBICACION_DENTRO_NOTAS = 'dentro_notas';
    const UBICACION_ENTRE_SECCIONES = 'entre_secciones';

    // Orientaciones
    const ORIENTACION_HORIZONTAL = 'horizontal';
    const ORIENTACION_VERTICAL = 'vertical';

    // Tipos de dispositivos
    const DISPOSITIVO_TODOS = 'todos';
    const DISPOSITIVO_DESKTOP = 'desktop';
    const DISPOSITIVO_MOVIL = 'movil';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las ubicaciones disponibles
     */
    public static function getUbicaciones() {
        return [
            self::UBICACION_INICIO => 'Inicio (Entre secciones)',
            self::UBICACION_SIDEBAR => 'Sidebar lateral derecho',
            self::UBICACION_FOOTER => 'Sección inferior (Footer)',
            self::UBICACION_DENTRO_NOTAS => 'Dentro de notas/artículos',
            self::UBICACION_ENTRE_SECCIONES => 'Entre títulos o bloques'
        ];
    }

    /**
     * Obtiene todas las orientaciones disponibles
     */
    public static function getOrientaciones() {
        return [
            self::ORIENTACION_HORIZONTAL => 'Horizontal',
            self::ORIENTACION_VERTICAL => 'Vertical'
        ];
    }

    /**
     * Obtiene todos los tipos de dispositivos
     */
    public static function getDispositivos() {
        return [
            self::DISPOSITIVO_TODOS => 'Todos los dispositivos',
            self::DISPOSITIVO_DESKTOP => 'Solo Desktop',
            self::DISPOSITIVO_MOVIL => 'Solo Móvil'
        ];
    }

    /**
     * Obtiene todos los banners
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
        
        $query .= " ORDER BY orden ASC, fecha_creacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un banner por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene banners activos por ubicación
     */
    public function getByUbicacion($ubicacion, $limit = null) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE activo = 1 
                  AND ubicacion = :ubicacion
                  AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())
                  AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
                  ORDER BY orden ASC, fecha_creacion DESC";
        
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':ubicacion', $ubicacion, PDO::PARAM_STR);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo banner
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, tipo, imagen_url, url_destino, ubicacion, orientacion, 
                   dispositivo, orden, activo, fecha_inicio, fecha_fin, rotativo) 
                  VALUES (:nombre, :tipo, :imagen_url, :url_destino, :ubicacion, :orientacion,
                         :dispositivo, :orden, :activo, :fecha_inicio, :fecha_fin, :rotativo)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'] ?? 'imagen',
            'imagen_url' => $data['imagen_url'] ?? null,
            'url_destino' => $data['url_destino'] ?? null,
            'ubicacion' => $data['ubicacion'],
            'orientacion' => $data['orientacion'] ?? self::ORIENTACION_HORIZONTAL,
            'dispositivo' => $data['dispositivo'] ?? self::DISPOSITIVO_TODOS,
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1,
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'rotativo' => $data['rotativo'] ?? 0
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza un banner
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['nombre', 'tipo', 'imagen_url', 'url_destino', 'ubicacion', 
                          'orientacion', 'dispositivo', 'orden', 'activo', 
                          'fecha_inicio', 'fecha_fin', 'rotativo'];
        
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
     * Elimina un banner
     */
    public function delete($id) {
        // Obtener el banner para eliminar la imagen
        $banner = $this->getById($id);
        
        if ($banner && !empty($banner['imagen_url'])) {
            // Validar que la ruta de imagen no contiene secuencias peligrosas
            $imagePath = $banner['imagen_url'];
            if (strpos($imagePath, '..') === false && strpos($imagePath, '/public/uploads/banners/') === 0) {
                $fullPath = __DIR__ . '/../../' . $imagePath;
                $realPath = realpath(dirname($fullPath));
                $expectedPath = realpath(__DIR__ . '/../../public/uploads/banners');
                
                // Verificar que la ruta real está dentro del directorio permitido
                if ($realPath && $expectedPath && strpos($realPath, $expectedPath) === 0) {
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        }
        
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Activa o desactiva un banner
     */
    public function toggleActivo($id) {
        $query = "UPDATE {$this->table} SET activo = NOT activo WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Incrementa el contador de impresiones
     */
    public function incrementarImpresiones($id) {
        $query = "UPDATE {$this->table} SET impresiones = impresiones + 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Incrementa el contador de clics
     */
    public function incrementarClics($id) {
        $query = "UPDATE {$this->table} SET clics = clics + 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene estadísticas de un banner
     */
    public function getEstadisticas($id) {
        $banner = $this->getById($id);
        
        if (!$banner) {
            return null;
        }
        
        $ctr = 0;
        if ($banner['impresiones'] > 0) {
            $ctr = ($banner['clics'] / $banner['impresiones']) * 100;
        }
        
        return [
            'impresiones' => $banner['impresiones'],
            'clics' => $banner['clics'],
            'ctr' => round($ctr, 2)
        ];
    }
}
