<?php
/**
 * Modelo MenuItem
 * Gestión de ítems del menú principal
 */

class MenuItem {
    private $db;
    private $table = 'menu_items';
    
    // Constantes para estados
    const CATEGORIA_VISIBLE = 1;
    const ITEM_ACTIVO = 1;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los ítems del menú con información de categoría y subcategorías
     */
    public function getAll($activo = null) {
        $query = "SELECT mi.*, c.nombre as categoria_nombre, c.slug as categoria_slug, c.descripcion as categoria_descripcion
                  FROM {$this->table} mi
                  INNER JOIN categorias c ON mi.categoria_id = c.id
                  WHERE c.visible = " . self::CATEGORIA_VISIBLE;
        
        if ($activo !== null) {
            $query .= " AND mi.activo = :activo";
        }
        
        $query .= " ORDER BY mi.orden ASC, c.nombre ASC";
        
        $stmt = $this->db->prepare($query);
        
        if ($activo !== null) {
            $stmt->execute(['activo' => $activo]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene ítems del menú con estructura jerárquica (incluye subcategorías)
     */
    public function getAllWithSubcategories($activo = null) {
        $menuItems = $this->getAll($activo);
        $categoriaModel = new Categoria();
        
        // Para cada ítem del menú, obtener sus subcategorías si existen
        foreach ($menuItems as &$item) {
            $item['subcategorias'] = $categoriaModel->getChildren($item['categoria_id'], 1);
        }
        
        return $menuItems;
    }

    /**
     * Obtiene un ítem por ID
     */
    public function getById($id) {
        $query = "SELECT mi.*, c.nombre as categoria_nombre, c.slug as categoria_slug
                  FROM {$this->table} mi
                  INNER JOIN categorias c ON mi.categoria_id = c.id
                  WHERE mi.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene un ítem por categoría
     */
    public function getByCategoriaId($categoriaId) {
        $query = "SELECT * FROM {$this->table} WHERE categoria_id = :categoria_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['categoria_id' => $categoriaId]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo ítem de menú
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (categoria_id, orden, activo) 
                  VALUES (:categoria_id, :orden, :activo)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'categoria_id' => $data['categoria_id'],
            'orden' => $data['orden'] ?? 0,
            'activo' => $data['activo'] ?? 1
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza un ítem de menú
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['categoria_id', 'orden', 'activo'];
        
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
     * Elimina un ítem de menú
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Sincroniza los ítems del menú con las categorías principales
     * Crea ítems para categorías que no tienen y los marca como activos por defecto
     */
    public function syncWithCategories() {
        // Obtener todas las categorías principales
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->getParents();
        
        foreach ($categorias as $categoria) {
            // Verificar si ya existe un ítem de menú para esta categoría
            $existente = $this->getByCategoriaId($categoria['id']);
            
            if (!$existente) {
                // Crear un nuevo ítem de menú
                $this->create([
                    'categoria_id' => $categoria['id'],
                    'orden' => $categoria['orden'] ?? 0,
                    'activo' => 1 // Por defecto activo
                ]);
            }
        }
        
        return true;
    }

    /**
     * Verifica si existe un ítem de menú para una categoría
     */
    public function existsForCategoria($categoriaId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE categoria_id = :categoria_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['categoria_id' => $categoriaId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
