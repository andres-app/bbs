<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productos = [
    1 => [
        'id' => 1,
        'nombre' => 'Set Natural 3.0 Triple Pack',
        'precio' => 189.90,
        'imagen' => 'https://infanti.com.pe/cdn/shop/files/01451483827-1.webp?v=1771948944&width=1220',
        'descripcion' => 'Set Natural 3.0 Philips Avent con 3 biberones diseñados para una alimentación más natural y cómoda.',
        'categoria' => 'Biberones',
        'enlace' => 'https://infanti.com.pe/collections/biberones-chupones-y-tetinas/products/set-natural-3-0-triple-pack',
        'stock' => 1
    ],
    2 => [
        'id' => 2,
        'nombre' => 'Bolsas Preesterilizadas X25 P/Leche Materna 6Oz/18',
        'precio' => 59.00,
        'imagen' => 'https://infanti.com.pe/cdn/shop/files/01451460325-3.jpg?v=1739393891&width=610',
        'descripcion' => 'Las bolsas de almacenamiento para leche materna Philips Avent le permiten almacenar su leche materna de forma segura y confiable.',
        'categoria' => 'Accesorios de lactancia',
        'enlace' => 'https://infanti.com.pe/collections/accesorios-3/products/scf603-25-bolsas-preesterilizadas-x25-p-leche-materna-6oz-18',
        'stock' => 1
    ],
    3 => [
        'id' => 3,
        'nombre' => 'Calentador de Biberones Manual Avent',
        'precio' => 329.00,
        'imagen' => 'https://infanti.com.pe/cdn/shop/files/01451435507_1.jpg?v=1694572416&width=1220',
        'descripcion' => '¡En 3 minutos caliente de forma delicada la leche o la comida de tu bebé!',
        'categoria' => 'Accesorios de lactancia',
        'enlace' => 'https://infanti.com.pe/collections/accesorios-3/products/calentador-de-biberones-manual-avent',
        'stock' => 1
    ],
    4 => [
        'id' => 4,
        'nombre' => 'Chupón Ultra Air 0-6 x2 Niña',
        'precio' => 65.00,
        'imagen' => 'https://infanti.com.pe/cdn/shop/files/01451408559-1.webp?v=1771280195&width=1220',
        'descripcion' => 'Deja que la piel de tu pequeño respire',
        'categoria' => 'Chupones',
        'enlace' => 'https://infanti.com.pe/collections/chupones/products/chupon-ultra-air-0-6-x2-nina',
        'stock' => 1
    ],
    5 => [
        'id' => 5,
        'nombre' => 'AIMAMA – MOCHILA PAÑALERA – GRIS',
        'precio' => 180.00,
        'imagen' => 'https://babyology.com.pe/wp-content/uploads/2021/01/mochila-jpg.webp',
        'descripcion' => 'deal para llevar las cosas del bebé a todas partes de una manera super cómoda.
        Hecha de Poliéster Impermeable. Interior: Impermeable.  Tiene múltiples bolsillos.
        Compartimiento térmico para 3 biberones independientes.  Compartimento para prendas húmedas.  Compartimentos especial para tener a la mano los pañitos húmedos.  Bolsillo trasero que permite fácil acceso al  fondo de la mochila sin sacar nada.',
        'categoria' => 'Pañaleros',
        'enlace' => 'https://babyology.com.pe/product/aimama-mochila-panalera-gris/',
        'stock' => 1
    ],
    6 => [
        'id' => 6,
        'nombre' => 'Cambiador Portátil Rosado Cometa - Mat',
        'precio' => 79.90,
        'imagen' => 'https://infanti.com.pe/cdn/shop/files/43A0MAT24CO-1.jpg?v=1758570083&width=1220',
        'descripcion' => 'El Cambiador Portátil Maternelle es el accesorio infaltable en las salidas con tu bebé. Brinda comodidad y practicidad al momento del cambio del pañal debido a que se adapta a diferentes situaciones.',
        'categoria' => 'Higiene',
        'enlace' => 'https://infanti.com.pe/collections/bolsos-panalero-1/products/cambiador-portatil-rosado-cometa-mat',
        'stock' => 1
    ],
];

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (!isset($_SESSION['regalos_confirmados'])) {
    $_SESSION['regalos_confirmados'] = [];
}

function h($text): string
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function cantidadReservadaGlobal(int $productoId): int
{
    $reservados = 0;
    if (!empty($_SESSION['regalos_confirmados'])) {
        foreach ($_SESSION['regalos_confirmados'] as $r) {
            if ((int)$r['producto_id'] === $productoId) {
                $reservados += (int)$r['cantidad'];
            }
        }
    }
    return $reservados;
}

function stockDisponible(int $productoId, array $productos): int
{
    $stockBase = (int)($productos[$productoId]['stock'] ?? 0);
    $reservados = cantidadReservadaGlobal($productoId);
    $enCarrito = (int)($_SESSION['carrito'][$productoId] ?? 0);

    return max(0, $stockBase - $reservados - $enCarrito);
}

function carritoResumen(array $productos): array
{
    $items = 0;
    $total = 0;

    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        if (isset($productos[$id])) {
            $items += (int)$cantidad;
            $total += ((float)$productos[$id]['precio'] * (int)$cantidad);
        }
    }

    return [
        'items' => $items,
        'total' => $total
    ];
}
