<?php
/**
 * Modelo Noticia
 * Gestión de noticias del portal
 */

class Noticia {
    private $db;
    private $table = 'noticias';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las noticias con paginación
     */
    public function getAll($estado = null, $categoriaId = null, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT n.*, u.nombre as autor_nombre, u.apellidos as autor_apellidos, 
                         c.nombre as categoria_nombre, c.slug as categoria_slug
                  FROM {$this->table} n
                  INNER JOIN usuarios u ON n.autor_id = u.id
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($estado !== null) {
            $query .= " AND n.estado = :estado";
            $params['estado'] = $estado;
            
            // If filtering by 'publicado' status, ensure scheduled news show only when time has come
            if ($estado === 'publicado') {
                $query .= " AND (n.fecha_publicacion IS NOT NULL 
                            AND (n.fecha_programada IS NULL OR n.fecha_programada <= NOW()))";
            }
        }
        
        if ($categoriaId !== null) {
            $query .= " AND n.categoria_id = :categoria_id";
            $params['categoria_id'] = $categoriaId;
        }
        
        $query .= " ORDER BY n.fecha_creacion DESC LIMIT :limit OFFSET :offset";
        
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
     * Cuenta el total de noticias
     */
    public function count($estado = null, $categoriaId = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($estado !== null) {
            $query .= " AND estado = :estado";
            $params['estado'] = $estado;
        }
        
        if ($categoriaId !== null) {
            $query .= " AND categoria_id = :categoria_id";
            $params['categoria_id'] = $categoriaId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }

    /**
     * Obtiene una noticia por ID
     */
    public function getById($id) {
        $query = "SELECT n.*, u.nombre as autor_nombre, u.apellidos as autor_apellidos,
                         c.nombre as categoria_nombre, c.slug as categoria_slug
                  FROM {$this->table} n
                  INNER JOIN usuarios u ON n.autor_id = u.id
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE n.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene una noticia por slug
     */
    public function getBySlug($slug) {
        $query = "SELECT n.*, u.nombre as autor_nombre, u.apellidos as autor_apellidos,
                         c.nombre as categoria_nombre
                  FROM {$this->table} n
                  INNER JOIN usuarios u ON n.autor_id = u.id
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE n.slug = :slug";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva noticia
     */
    public function create($data) {
        // Si está programada para publicarse en el futuro, no establecer fecha_publicacion aún
        $fecha_publicacion = null;
        if (($data['estado'] ?? 'borrador') === 'publicado') {
            $fecha_programada = $data['fecha_programada'] ?? null;
            if (!$fecha_programada || strtotime($fecha_programada) <= time()) {
                // Publicar inmediatamente si no hay programación o la fecha ya pasó
                $fecha_publicacion = date('Y-m-d H:i:s');
            }
            // Si está programada para el futuro, fecha_publicacion permanece NULL
        }
        
        $query = "INSERT INTO {$this->table} 
                  (titulo, subtitulo, slug, contenido, resumen, tags, autor_id, categoria_id, 
                   imagen_destacada, video_url, video_youtube, video_thumbnail, video_thumbnail_url, estado, destacado, 
                   permitir_comentarios, fecha_programada, fecha_publicacion) 
                  VALUES (:titulo, :subtitulo, :slug, :contenido, :resumen, :tags, :autor_id, :categoria_id,
                          :imagen_destacada, :video_url, :video_youtube, :video_thumbnail, :video_thumbnail_url, :estado, :destacado, 
                          :permitir_comentarios, :fecha_programada, :fecha_publicacion)";
        
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            'titulo' => $data['titulo'],
            'subtitulo' => $data['subtitulo'] ?? null,
            'slug' => $this->generateSlug($data['slug'] ?? $data['titulo']),
            'contenido' => $data['contenido'],
            'resumen' => $data['resumen'] ?? null,
            'tags' => $data['tags'] ?? null,
            'autor_id' => $data['autor_id'],
            'categoria_id' => $data['categoria_id'],
            'imagen_destacada' => $data['imagen_destacada'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'video_youtube' => $data['video_youtube'] ?? null,
            'video_thumbnail' => $data['video_thumbnail'] ?? null,
            'video_thumbnail_url' => $data['video_thumbnail_url'] ?? null,
            'estado' => $data['estado'] ?? 'borrador',
            'destacado' => $data['destacado'] ?? 0,
            'permitir_comentarios' => $data['permitir_comentarios'] ?? 1,
            'fecha_programada' => $data['fecha_programada'] ?? null,
            'fecha_publicacion' => $fecha_publicacion
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualiza una noticia
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['titulo', 'subtitulo', 'slug', 'contenido', 'resumen', 'tags',
                          'categoria_id', 'imagen_destacada', 'video_url', 'video_youtube', 
                          'video_thumbnail', 'video_thumbnail_url', 'estado', 'destacado', 'orden_destacado', 
                          'permitir_comentarios', 'fecha_programada', 
                          'modificado_por'];
        
        // Manejar fecha_publicacion según el estado y fecha_programada
        if (isset($data['estado']) && $data['estado'] === 'publicado') {
            $fecha_programada = $data['fecha_programada'] ?? null;
            if (!$fecha_programada || strtotime($fecha_programada) <= time()) {
                // Si no hay programación o la fecha ya pasó, publicar inmediatamente
                // Solo establecer fecha_publicacion si aún no tiene una
                $noticia = $this->getById($id);
                if (!$noticia['fecha_publicacion']) {
                    $fields[] = "fecha_publicacion = NOW()";
                }
            } else {
                // Si está programada para el futuro, resetear fecha_publicacion a NULL
                // para que el script publicar_programadas.php la procese
                $fields[] = "fecha_publicacion = NULL";
            }
        }
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'slug') {
                    $fields[] = "$field = :$field";
                    $params[$field] = $this->generateSlug($data[$field]);
                } else {
                    $fields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        // Incrementar versión
        $fields[] = "version = version + 1";
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }

    /**
     * Elimina una noticia
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cambia el estado de una noticia
     */
    public function changeStatus($id, $estado, $usuarioId) {
        $query = "UPDATE {$this->table} 
                  SET estado = :estado, modificado_por = :modificado_por";
        
        // Si se publica, establecer fecha de publicación
        if ($estado === 'publicado') {
            $query .= ", fecha_publicacion = NOW()";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'estado' => $estado,
            'modificado_por' => $usuarioId
        ]);
    }

    /**
     * Obtiene noticias destacadas
     */
    public function getDestacadas($limit = 5) {
        $query = "SELECT n.*, c.nombre as categoria_nombre
                  FROM {$this->table} n
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE n.destacado = 1 AND n.estado = 'publicado'
                  AND n.fecha_publicacion IS NOT NULL
                  AND (n.fecha_programada IS NULL OR n.fecha_programada <= NOW())
                  ORDER BY n.orden_destacado ASC, n.fecha_publicacion DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las noticias más leídas
     */
    public function getMasLeidas($limit = 10) {
        $query = "SELECT n.*, c.nombre as categoria_nombre
                  FROM {$this->table} n
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE n.estado = 'publicado'
                  AND n.fecha_publicacion IS NOT NULL
                  AND (n.fecha_programada IS NULL OR n.fecha_programada <= NOW())
                  ORDER BY n.visitas DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Incrementa las visitas de una noticia
     */
    public function incrementVisitas($id) {
        $query = "UPDATE {$this->table} SET visitas = visitas + 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
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
     * Guarda una versión del contenido
     */
    public function saveVersion($noticiaId, $titulo, $subtitulo, $contenido, $usuarioId, $comentario = null) {
        $query = "INSERT INTO noticias_versiones 
                  (noticia_id, titulo, subtitulo, contenido, version, modificado_por, comentario)
                  SELECT :noticia_id, :titulo, :subtitulo, :contenido, 
                         COALESCE(MAX(version), 0) + 1, :modificado_por, :comentario
                  FROM noticias_versiones WHERE noticia_id = :noticia_id2";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'noticia_id' => $noticiaId,
            'noticia_id2' => $noticiaId,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'contenido' => $contenido,
            'modificado_por' => $usuarioId,
            'comentario' => $comentario
        ]);
    }

    /**
     * Obtiene las versiones de una noticia
     */
    public function getVersions($noticiaId) {
        $query = "SELECT v.*, u.nombre, u.apellidos
                  FROM noticias_versiones v
                  INNER JOIN usuarios u ON v.modificado_por = u.id
                  WHERE v.noticia_id = :noticia_id
                  ORDER BY v.version DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['noticia_id' => $noticiaId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca noticias por término
     */
    public function search($termino, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT n.*, c.nombre as categoria_nombre
                  FROM {$this->table} n
                  INNER JOIN categorias c ON n.categoria_id = c.id
                  WHERE n.estado = 'publicado' 
                  AND (n.titulo LIKE :termino1 
                       OR n.contenido LIKE :termino2 
                       OR n.resumen LIKE :termino3 
                       OR n.tags LIKE :termino4)
                  ORDER BY n.fecha_publicacion DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $searchTerm = '%' . $termino . '%';
        $stmt->bindValue(':termino1', $searchTerm);
        $stmt->bindValue(':termino2', $searchTerm);
        $stmt->bindValue(':termino3', $searchTerm);
        $stmt->bindValue(':termino4', $searchTerm);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cuenta resultados de búsqueda
     */
    public function countSearch($termino) {
        $query = "SELECT COUNT(*) as total 
                  FROM {$this->table} 
                  WHERE estado = 'publicado' 
                  AND (titulo LIKE :termino1 
                       OR contenido LIKE :termino2 
                       OR resumen LIKE :termino3 
                       OR tags LIKE :termino4)";
        
        $stmt = $this->db->prepare($query);
        $searchTerm = '%' . $termino . '%';
        $stmt->execute([
            'termino1' => $searchTerm,
            'termino2' => $searchTerm,
            'termino3' => $searchTerm,
            'termino4' => $searchTerm
        ]);
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
