<?php
require_once __DIR__ . '/data.php';

$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'quitar') {
        $id = (int)($_POST['producto_id'] ?? 0);

        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]--;

            if ($_SESSION['carrito'][$id] <= 0) {
                unset($_SESSION['carrito'][$id]);
            }

            $mensaje = 'Producto retirado del carrito.';
        }
    }

    if ($accion === 'vaciar_carrito') {
        $_SESSION['carrito'] = [];
        $mensaje = 'Carrito vaciado correctamente.';
    }

    if ($accion === 'confirmar_regalo') {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $metodo_pago = trim($_POST['metodo_pago'] ?? '');
        $comentario = trim($_POST['comentario'] ?? '');

        if ($nombre === '' || $telefono === '' || $metodo_pago === '') {
            $mensaje = 'Completa tu nombre, teléfono y método de pago.';
            $tipoMensaje = 'error';
        } elseif (empty($_SESSION['carrito'])) {
            $mensaje = 'Tu carrito está vacío.';
            $tipoMensaje = 'error';
        } else {
            $ok = true;

            foreach ($_SESSION['carrito'] as $productoId => $cantidad) {
                $stockBase = (int)($productos[$productoId]['stock'] ?? 0);
                $reservados = cantidadReservadaGlobal((int)$productoId);
                $disponibleReal = $stockBase - $reservados;

                if ((int)$cantidad > $disponibleReal) {
                    $ok = false;
                    break;
                }
            }

            if (!$ok) {
                $mensaje = 'Uno de los productos ya no tiene stock suficiente.';
                $tipoMensaje = 'error';
            } else {
                foreach ($_SESSION['carrito'] as $productoId => $cantidad) {
                    $_SESSION['regalos_confirmados'][] = [
                        'producto_id' => (int)$productoId,
                        'cantidad' => (int)$cantidad,
                        'nombre' => $nombre,
                        'telefono' => $telefono,
                        'metodo_pago' => $metodo_pago,
                        'comentario' => $comentario,
                        'fecha' => date('Y-m-d H:i:s')
                    ];
                }

                $_SESSION['carrito'] = [];
                $mensaje = 'Tu regalo fue confirmado correctamente. Muchas gracias.';
                $tipoMensaje = 'success';
            }
        }
    }
}

$resumen = carritoResumen($productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Baby Shower</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bbpink: '#F9DCE7',
                        bbrose: '#F4BACB',
                        bbblue: '#D9ECFF',
                        bbsky: '#B8DDFB',
                        bbcream: '#FFF8F4',
                        bbmint: '#DDF4EC',
                        bbtext: '#5F5A68',
                        bbstrong: '#7A6170'
                    },
                    boxShadow: {
                        soft: '0 12px 35px rgba(185, 166, 180, 0.16)',
                        card: '0 18px 40px rgba(181, 200, 227, 0.18)'
                    }
                }
            }
        }
    </script>
    <style>
        body{
            background:
                radial-gradient(circle at top left, rgba(249,220,231,.70), transparent 28%),
                radial-gradient(circle at top right, rgba(217,236,255,.75), transparent 28%),
                linear-gradient(180deg, #fffafc 0%, #ffffff 45%, #fff7fb 100%);
        }
    </style>
</head>
<body class="text-bbtext min-h-screen">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-white/80 border-b border-white/60">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-4">
                <div>
                    <div class="text-xs uppercase tracking-[.22em] text-bbstrong/70 font-bold">Baby Shower</div>
                    <h1 class="text-lg md:text-2xl font-black text-bbstrong">
                        Checkout de regalo
                    </h1>
                </div>

                <a href="index.php" class="rounded-full bg-white border border-bbpink/40 px-5 py-3 font-semibold text-bbstrong shadow-soft">
                    Volver a regalos
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 md:px-6 lg:px-8">

        <?php if ($mensaje !== ''): ?>
            <div class="mb-5 rounded-2xl px-5 py-4 shadow-soft border <?php echo $tipoMensaje === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?>">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="grid lg:grid-cols-[1.15fr_.85fr] gap-6">
            <!-- CARRITO -->
            <div class="rounded-[30px] bg-white/90 border border-white shadow-soft p-6">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h2 class="text-2xl font-black text-bbstrong">Resumen del carrito</h2>
                        <p class="text-bbtext/80 mt-1">Verifica tu selección antes del pago.</p>
                    </div>

                    <?php if (!empty($_SESSION['carrito'])): ?>
                        <form method="POST">
                            <input type="hidden" name="accion" value="vaciar_carrito">
                            <button type="submit" class="rounded-full border border-rose-200 bg-rose-50 text-rose-700 px-4 py-2 font-semibold">
                                Vaciar carrito
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (empty($_SESSION['carrito'])): ?>
                        <div class="rounded-[24px] border border-dashed border-bbrose bg-bbpink/20 p-8 text-center">
                            <div class="text-lg font-bold text-bbstrong">Tu carrito está vacío</div>
                            <p class="mt-2 text-bbtext/80">Agrega productos desde la página principal.</p>
                            <a href="index.php" class="inline-block mt-4 rounded-full bg-bbstrong text-white px-5 py-3 font-semibold">
                                Ir a regalos
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
                            <?php if (isset($productos[$id])): ?>
                                <div class="rounded-[24px] bg-bbcream border border-bbpink/30 p-4 md:p-5 flex flex-col md:flex-row gap-4 md:items-center md:justify-between">
                                    <div class="flex gap-4">
                                        <img src="<?php echo h($productos[$id]['imagen']); ?>" class="w-24 h-24 rounded-2xl object-cover" alt="">
                                        <div>
                                            <h3 class="text-lg font-extrabold text-bbstrong">
                                                <?php echo h($productos[$id]['nombre']); ?>
                                            </h3>
                                            <p class="text-sm text-bbtext/75 mt-1">
                                                <?php echo h($productos[$id]['categoria']); ?>
                                            </p>
                                            <div class="mt-2 text-sm">
                                                Cantidad: <strong><?php echo (int)$cantidad; ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between md:justify-end gap-4">
                                        <div class="text-right">
                                            <div class="text-xs text-bbtext/70">Subtotal</div>
                                            <div class="text-xl font-black text-bbstrong">
                                                S/ <?php echo number_format((float)$productos[$id]['precio'] * (int)$cantidad, 2); ?>
                                            </div>
                                        </div>

                                        <form method="POST">
                                            <input type="hidden" name="accion" value="quitar">
                                            <input type="hidden" name="producto_id" value="<?php echo (int)$id; ?>">
                                            <button type="submit" class="rounded-full bg-white border border-slate-200 px-4 py-2 font-semibold text-slate-700">
                                                Quitar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <div class="rounded-[26px] bg-gradient-to-r from-bbpink/40 to-bbblue/40 p-5 border border-white">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-bbstrong">Total a regalar</span>
                                <span class="text-3xl font-black text-bbstrong">
                                    S/ <?php echo number_format($resumen['total'], 2); ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PAGO -->
            <div class="rounded-[30px] bg-white/90 border border-white shadow-soft p-6">
                <h2 class="text-2xl font-black text-bbstrong">Pago y confirmación</h2>
                <p class="text-bbtext/80 mt-1">
                    Paga por Yape o Plin y luego registra tus datos.
                </p>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-[24px] bg-[#F5ECFF] p-4 border border-purple-100 text-center">
                        <div class="text-lg font-extrabold text-purple-700">Yape</div>
                        <div class="mt-3 rounded-2xl bg-white p-3 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=YAPE-BABYSHOWER" alt="QR Yape" class="w-full rounded-xl">
                        </div>
                    </div>

                    <div class="rounded-[24px] bg-[#EAF5FF] p-4 border border-sky-100 text-center">
                        <div class="text-lg font-extrabold text-sky-700">Plin</div>
                        <div class="mt-3 rounded-2xl bg-white p-3 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=PLIN-BABYSHOWER" alt="QR Plin" class="w-full rounded-xl">
                        </div>
                    </div>
                </div>

                <form method="POST" class="mt-6 space-y-4">
                    <input type="hidden" name="accion" value="confirmar_regalo">

                    <div>
                        <label class="block text-sm font-bold text-bbstrong mb-2">Nombre de quien regala</label>
                        <input
                            type="text"
                            name="nombre"
                            class="w-full rounded-2xl border border-bbrose/40 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-bbsky"
                            placeholder="Ej. María Fernanda"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-bbstrong mb-2">Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            class="w-full rounded-2xl border border-bbrose/40 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-bbsky"
                            placeholder="Ej. 987654321"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-bbstrong mb-2">Método de pago</label>
                        <select
                            name="metodo_pago"
                            class="w-full rounded-2xl border border-bbrose/40 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-bbsky"
                            required
                        >
                            <option value="">Selecciona</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-bbstrong mb-2">Mensaje o dedicatoria</label>
                        <textarea
                            name="comentario"
                            rows="4"
                            class="w-full rounded-2xl border border-bbrose/40 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-bbsky"
                            placeholder="Con mucho cariño para el bebé..."
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-bbstrong text-white py-3.5 font-bold text-lg shadow-soft hover:opacity-90 transition"
                        <?php echo empty($_SESSION['carrito']) ? 'disabled' : ''; ?>
                    >
                        Confirmar regalo
                    </button>
                </form>
            </div>
        </section>
    </div>
</body>
</html>