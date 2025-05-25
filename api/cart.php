<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../config/db_connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado']);
    exit;
}

$userId = $_SESSION['user_id'];

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si hay una acción especificada
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'No se especificó ninguna acción']);
        exit;
    }

    // Obtener la acción
    $action = $_POST['action'];

    // Procesar la acción
    switch ($action) {
        case 'add_to_cart':
            addToCart();
            break;
        case 'remove_from_cart':
            removeFromCart();
            break;
        case 'update_quantity':
            updateQuantity();
            break;
        case 'checkout':
            checkout();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} else {
    // Si es una solicitud GET, obtener el carrito
    getCart();
}

/**
 * Añade un producto al carrito
 */
function addToCart() {
    global $conn, $userId;
    
    // Verificar si se proporcionaron los datos necesarios
    if (!isset($_POST['song_id']) && !isset($_POST['album_id']) && !isset($_POST['sencillo_id'])) {
        echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID de producto']);
        exit;
    }
    
    try {
        // Determinar el tipo de producto
        if (isset($_POST['song_id'])) {
            $productId = $_POST['song_id'];
            $productType = 'cancion';
        } elseif (isset($_POST['album_id'])) {
            $productId = $_POST['album_id'];
            $productType = 'album';
        } elseif (isset($_POST['sencillo_id'])) {
            $productId = $_POST['sencillo_id'];
            $productType = 'sencillo';
        }
        
        // Verificar si el producto ya está en el carrito
        $stmt = $conn->prepare("
            SELECT id_carrito FROM carrito 
            WHERE id_usuario = :user_id AND id_producto = :product_id AND tipo_producto = :product_type
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':product_type', $productType, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // El producto ya está en el carrito, actualizar cantidad
            $stmt = $conn->prepare("
                UPDATE carrito 
                SET cantidad = cantidad + 1 
                WHERE id_usuario = :user_id AND id_producto = :product_id AND tipo_producto = :product_type
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':product_type', $productType, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Cantidad actualizada en el carrito']);
        } else {
            // El producto no está en el carrito, añadirlo
            $stmt = $conn->prepare("
                INSERT INTO carrito (id_usuario, id_producto, tipo_producto, cantidad) 
                VALUES (:user_id, :product_id, :product_type, 1)
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':product_type', $productType, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Producto añadido al carrito']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al añadir al carrito: ' . $e->getMessage()]);
    }
}

/**
 * Elimina un producto del carrito
 */
function removeFromCart() {
    global $conn, $userId;
    
    // Verificar si se proporcionó un ID de carrito
    if (!isset($_POST['cart_id'])) {
        echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID de carrito']);
        exit;
    }
    
    $cartId = $_POST['cart_id'];
    
    try {
        // Verificar que el carrito pertenece al usuario
        $stmt = $conn->prepare("
            SELECT id_carrito FROM carrito 
            WHERE id_carrito = :cart_id AND id_usuario = :user_id
        ");
        $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'El producto no está en tu carrito']);
            exit;
        }
        
        // Eliminar el producto del carrito
        $stmt = $conn->prepare("DELETE FROM carrito WHERE id_carrito = :cart_id");
        $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito: ' . $e->getMessage()]);
    }
}

/**
 * Actualiza la cantidad de un producto en el carrito
 */
function updateQuantity() {
    global $conn, $userId;
    
    // Verificar si se proporcionaron los datos necesarios
    if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos necesarios']);
        exit;
    }
    
    $cartId = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validar cantidad
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'La cantidad debe ser al menos 1']);
        exit;
    }
    
    try {
        // Verificar que el carrito pertenece al usuario
        $stmt = $conn->prepare("
            SELECT id_carrito FROM carrito 
            WHERE id_carrito = :cart_id AND id_usuario = :user_id
        ");
        $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'El producto no está en tu carrito']);
            exit;
        }
        
        // Actualizar la cantidad
        $stmt = $conn->prepare("
            UPDATE carrito 
            SET cantidad = :quantity 
            WHERE id_carrito = :cart_id
        ");
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar cantidad: ' . $e->getMessage()]);
    }
}

/**
 * Realiza el proceso de compra
 */
function checkout() {
    global $conn, $userId;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Obtener productos del carrito
        $stmt = $conn->prepare("
            SELECT c.id_producto, c.tipo_producto, c.cantidad,
                   CASE 
                       WHEN c.tipo_producto = 'album' THEN a.precio
                       WHEN c.tipo_producto = 'cancion' THEN can.precio
                       WHEN c.tipo_producto = 'sencillo' THEN s.precio
                   END as precio
            FROM carrito c
            LEFT JOIN album a ON c.tipo_producto = 'album' AND c.id_producto = a.id_album
            LEFT JOIN canciones can ON c.tipo_producto = 'cancion' AND c.id_producto = can.id_cancion
            LEFT JOIN sencillos s ON c.tipo_producto = 'sencillo' AND c.id_producto = s.id_sencillo
            WHERE c.id_usuario = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $cartItems = $stmt->fetchAll();
        
        if (count($cartItems) === 0) {
            echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
            exit;
        }
        
        // Registrar cada compra
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("
                INSERT INTO compras (id_usuario, tipo_producto, id_producto, precio)
                VALUES (:user_id, :product_type, :product_id, :price)
                ON DUPLICATE KEY UPDATE precio = :price
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_type', $item['tipo_producto'], PDO::PARAM_STR);
            $stmt->bindParam(':product_id', $item['id_producto'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['precio'], PDO::PARAM_STR);
            $stmt->execute();
        }
        
        // Vaciar el carrito
        $stmt = $conn->prepare("DELETE FROM carrito WHERE id_usuario = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Confirmar transacción
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Compra realizada con éxito']);
        
    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()]);
    }
}

/**
 * Obtiene el contenido del carrito
 */
function getCart() {
    global $conn, $userId;
    
    try {
        // Obtener productos del carrito con detalles
        $stmt = $conn->prepare("
            SELECT c.id_carrito, c.id_producto, c.tipo_producto, c.cantidad,
                   CASE 
                       WHEN c.tipo_producto = 'album' THEN a.nombre_album
                       WHEN c.tipo_producto = 'cancion' THEN can.nombre_cancion
                       WHEN c.tipo_producto = 'sencillo' THEN s.nombre_sencillo
                   END as nombre,
                   CASE 
                       WHEN c.tipo_producto = 'album' THEN a.precio
                       WHEN c.tipo_producto = 'cancion' THEN can.precio
                       WHEN c.tipo_producto = 'sencillo' THEN s.precio
                   END as precio,
                   CASE 
                       WHEN c.tipo_producto = 'album' THEN a.imagen_album_path
                       WHEN c.tipo_producto = 'cancion' THEN alb.imagen_album_path
                       WHEN c.tipo_producto = 'sencillo' THEN s.imagen_sencillo_path
                   END as imagen,
                   CASE 
                       WHEN c.tipo_producto = 'album' THEN art_a.nombre_usuario
                       WHEN c.tipo_producto = 'cancion' THEN art_c.nombre_usuario
                       WHEN c.tipo_producto = 'sencillo' THEN art_s.nombre_usuario
                   END as artista
            FROM carrito c
            LEFT JOIN album a ON c.tipo_producto = 'album' AND c.id_producto = a.id_album
            LEFT JOIN canciones can ON c.tipo_producto = 'cancion' AND c.id_producto = can.id_cancion
            LEFT JOIN album alb ON can.id_album = alb.id_album
            LEFT JOIN sencillos s ON c.tipo_producto = 'sencillo' AND c.id_producto = s.id_sencillo
            LEFT JOIN artista art_a ON a.id_artista = art_a.id_artista
            LEFT JOIN usuario u_a ON art_a.usuario = u_a.id_usuario
            LEFT JOIN artista art_c ON can.id_artista = art_c.id_artista
            LEFT JOIN usuario u_c ON art_c.usuario = u_c.id_usuario
            LEFT JOIN artista art_s ON s.id_artista = art_s.id_artista
            LEFT JOIN usuario u_s ON art_s.usuario = u_s.id_usuario
            WHERE c.id_usuario = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $cartItems = $stmt->fetchAll();
        
        // Calcular total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        
        echo json_encode([
            'success' => true, 
            'cart' => $cartItems,
            'total' => $total
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener el carrito: ' . $e->getMessage()]);
    }
}
?>
