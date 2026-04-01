<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productos = [
    1 => [
        'id' => 1,
        'nombre' => 'Pañalera Premium',
        'precio' => 189.90,
        'imagen' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Bolso amplio, elegante y práctico para las salidas del bebé.',
        'categoria' => 'Esenciales',
        'stock' => 1
    ],
    2 => [
        'id' => 2,
        'nombre' => 'Set de Ropita Recién Nacido',
        'precio' => 129.90,
        'imagen' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Conjunto suave y delicado para los primeros días del bebé.',
        'categoria' => 'Ropita',
        'stock' => 2
    ],
    3 => [
        'id' => 3,
        'nombre' => 'Manta Antialérgica',
        'precio' => 79.90,
        'imagen' => 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Manta súper suave y abrigadora para el descanso del bebé.',
        'categoria' => 'Descanso',
        'stock' => 3
    ],
    4 => [
        'id' => 4,
        'nombre' => 'Kit de Biberones',
        'precio' => 99.90,
        'imagen' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Set práctico para la alimentación diaria del bebé.',
        'categoria' => 'Alimentación',
        'stock' => 2
    ],
    5 => [
        'id' => 5,
        'nombre' => 'Cuna Moisés Decorativa',
        'precio' => 259.90,
        'imagen' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Hermosa opción decorativa y funcional para el descanso.',
        'categoria' => 'Descanso',
        'stock' => 1
    ],
    6 => [
        'id' => 6,
        'nombre' => 'Pack de Toallitas y Aseo',
        'precio' => 59.90,
        'imagen' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80',
        'descripcion' => 'Todo lo necesario para el cuidado diario del bebé.',
        'categoria' => 'Higiene',
        'stock' => 4
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