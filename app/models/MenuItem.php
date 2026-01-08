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
     * Crea ítems para categorías que no tienen, actualiza orden, y elimina huérfanos
     */
    public function syncWithCategories() {
        $categoriaModel = new Categoria();
        $categoriasParent = $categoriaModel->getParents();
        
        // Crear array de IDs de categorías principales
        $categoriasIds = array_column($categoriasParent, 'id');
        
        // 1. Obtener todos los ítems de menú existentes
        $menuItemsExistentes = $this->getAll();
        
        // 2. Eliminar ítems huérfanos (categorías que ya no existen o que ahora son subcategorías)
        foreach ($menuItemsExistentes as $menuItem) {
            $categoria = $categoriaModel->getById($menuItem['categoria_id']);
            
            // Si la categoría no existe o ahora es una subcategoría, eliminar el ítem
            if (!$categoria || $categoria['padre_id'] !== null) {
                $this->delete($menuItem['id']);
            }
        }
        
        // 3. Crear o actualizar ítems para categorías principales
        foreach ($categoriasParent as $categoria) {
            $existente = $this->getByCategoriaId($categoria['id']);
            
            if ($existente) {
                // Actualizar orden si ha cambiado
                if ($existente['orden'] != ($categoria['orden'] ?? 0)) {
                    $this->update($existente['id'], [
                        'orden' => $categoria['orden'] ?? 0
                    ]);
                }
            } else {
                // Crear nuevo ítem de menú
                $this->create([
                    'categoria_id' => $categoria['id'],
                    'orden' => $categoria['orden'] ?? 0,
                    'activo' => $categoria['visible'] ? 1 : 0 // Respect category visibility
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
