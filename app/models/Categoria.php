<?php
/**
 * Modelo Categoria
 * Gestión de categorías y secciones
 */

class Categoria {
    private $db;
    private $table = 'categorias';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las categorías
     */
    public function getAll($visible = null) {
        $query = "SELECT c.*, u.nombre as editor_nombre, u.apellidos as editor_apellidos,
                         (SELECT COUNT(*) FROM categorias WHERE padre_id = c.id) as subcategorias_count
                  FROM {$this->table} c
                  LEFT JOIN usuarios u ON c.editor_responsable_id = u.id";
        
        if ($visible !== null) {
            $query .= " WHERE c.visible = :visible";
        }
        
        $query .= " ORDER BY c.orden ASC, c.nombre ASC";
        
        $stmt = $this->db->prepare($query);
        
        if ($visible !== null) {
            $stmt->execute(['visible' => $visible]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene categorías principales (sin padre)
     */
    public function getParents($visible = null) {
        $query = "SELECT c.*,
                         (SELECT COUNT(*) FROM categorias WHERE padre_id = c.id) as subcategorias_count
                  FROM {$this->table} c
                  WHERE c.padre_id IS NULL";
        
        if ($visible !== null) {
            $query .= " AND c.visible = :visible";
        }
        
        $query .= " ORDER BY c.orden ASC, c.nombre ASC";
        
        $stmt = $this->db->prepare($query);
        
        if ($visible !== null) {
            $stmt->execute(['visible' => $visible]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene subcategorías de una categoría padre
     */
    public function getChildren($padreId, $visible = null) {
        $query = "SELECT * FROM {$this->table} WHERE padre_id = :padre_id";
        
        if ($visible !== null) {
            $query .= " AND visible = :visible";
        }
        
        $query .= " ORDER BY orden ASC, nombre ASC";
        
        $stmt = $this->db->prepare($query);
        
        $params = ['padre_id' => $padreId];
        if ($visible !== null) {
            $params['visible'] = $visible;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una categoría por ID
     */
    public function getById($id) {
        $query = "SELECT c.*, u.nombre as editor_nombre, u.apellidos as editor_apellidos,
                         p.nombre as padre_nombre
                  FROM {$this->table} c
                  LEFT JOIN usuarios u ON c.editor_responsable_id = u.id
                  LEFT JOIN {$this->table} p ON c.padre_id = p.id
                  WHERE c.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene una categoría por slug
     */
    public function getBySlug($slug) {
        $query = "SELECT * FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva categoría
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, slug, descripcion, padre_id, orden, visible, editor_responsable_id) 
                  VALUES (:nombre, :slug, :descripcion, :padre_id, :orden, :visible, :editor_responsable_id)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'nombre' => $data['nombre'],
            'slug' => $this->generateSlug($data['slug'] ?? $data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'padre_id' => $data['padre_id'] ?? null,
            'orden' => $data['orden'] ?? 0,
            'visible' => $data['visible'] ?? 1,
            'editor_responsable_id' => $data['editor_responsable_id'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza una categoría
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['nombre', 'slug', 'descripcion', 'padre_id', 'orden', 
                          'visible', 'editor_responsable_id'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'slug') {
                    $fields[] = "$field = :$field";
                    $params[$field] = $this->generateSlug($data[$field], $id);
                } else {
                    $fields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
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
     * Elimina una categoría
     */
    public function delete($id) {
        // Verificar si tiene subcategorías
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE padre_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // No se puede eliminar si tiene subcategorías
        }
        
        // Verificar si tiene noticias asociadas
        $query = "SELECT COUNT(*) as count FROM noticias WHERE categoria_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // No se puede eliminar si tiene noticias
        }
        
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtiene el árbol jerárquico de categorías
     */
    public function getTree($visible = null) {
        $parents = $this->getParents($visible);
        $tree = [];
        
        foreach ($parents as $parent) {
            $parent['children'] = $this->getChildren($parent['id'], $visible);
            $tree[] = $parent;
        }
        
        return $tree;
    }

    /**
     * Genera un slug único a partir de un texto
     */
    private function generateSlug($text, $id = null) {
        // Convertir a minúsculas
        $slug = mb_strtolower($text, 'UTF-8');
        
        // Reemplazar caracteres especiales
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $slug
        );
        
        // Eliminar caracteres no alfanuméricos
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        
        // Reemplazar espacios y guiones múltiples
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        
        // Eliminar guiones al inicio y final
        $slug = trim($slug, '-');
        
        // Verificar si el slug ya existe
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";
        if ($id !== null) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($query);
        $params = ['slug' => $slug];
        if ($id !== null) {
            $params['id'] = $id;
        }
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        // Si existe, agregar un número
        if ($result['count'] > 0) {
            $counter = 1;
            $newSlug = $slug . '-' . $counter;
            
            while (true) {
                $stmt->execute(['slug' => $newSlug]);
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    return $newSlug;
                }
                
                $counter++;
                $newSlug = $slug . '-' . $counter;
            }
        }
        
        return $slug;
    }

    /**
     * Cuenta noticias por categoría
     */
    public function countNoticias($categoriaId, $estado = null) {
        $query = "SELECT COUNT(*) as total FROM noticias WHERE categoria_id = :categoria_id";
        $params = ['categoria_id' => $categoriaId];
        
        if ($estado !== null) {
            $query .= " AND estado = :estado";
            $params['estado'] = $estado;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
