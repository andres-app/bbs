<?php
require_once __DIR__ . '/data.php';

$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $id = (int)($_POST['producto_id'] ?? 0);

        if (isset($productos[$id])) {
            $disponible = stockDisponible($id, $productos);

            if ($disponible > 0) {
                $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;

                $_SESSION['flash_mensaje'] = 'Regalo agregado correctamente.';
                $_SESSION['flash_tipo'] = 'success';

                header('Location: checkout.php');
                exit;
            } else {
                $mensaje = 'Ese producto ya no tiene unidades disponibles.';
                $tipoMensaje = 'error';
            }
        }
    }

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
}

$resumen = carritoResumen($productos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Baby Shower | Lista de Regalos</title>
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
                        bbstrong: '#7A6170',
                        bbviolet: '#A78BFA'
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
        body {
            background:
                radial-gradient(circle at top left, rgba(249, 220, 231, .70), transparent 28%),
                radial-gradient(circle at top right, rgba(217, 236, 255, .75), transparent 28%),
                linear-gradient(180deg, #fffafc 0%, #ffffff 45%, #fff7fb 100%);
        }
    </style>
</head>

<body class="text-bbtext min-h-screen">

    <!-- TOP BAR / CARRITO -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-white/80 border-b border-white/60">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-4">
                <div class="min-w-0">
                    <div class="text-xs uppercase tracking-[.22em] text-bbstrong/70 font-bold">Baby Shower</div>
                    <h1 class="text-lg md:text-2xl font-black text-bbstrong truncate">
                        Lista de regalos del bebé
                    </h1>
                </div>

                <div class="flex items-center gap-3 shrink-0 relative">
                    <!-- MINI CART BUTTON -->
                    <button
                        type="button"
                        id="miniCartToggle"
                        class="relative flex items-center gap-3 rounded-full bg-white border border-bbpink/40 px-4 py-2 shadow-soft hover:shadow-md transition">
                        <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-bbpink to-bbblue flex items-center justify-center text-bbstrong text-lg">
                            🛒
                            <?php if ($resumen['items'] > 0): ?>
                                <span class="absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1 rounded-full bg-bbstrong text-white text-[11px] font-bold flex items-center justify-center">
                                    <?php echo $resumen['items']; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="hidden md:block leading-tight text-left">
                            <div class="text-xs text-bbtext/70 font-semibold">Carrito</div>
                            <div class="text-sm font-bold text-bbstrong">
                                S/ <?php echo number_format($resumen['total'], 2); ?>
                            </div>
                        </div>
                    </button>

                    <a href="checkout.php" class="rounded-full bg-bbstrong text-white px-5 py-3 font-semibold shadow-soft hover:opacity-90 transition">
                        Ir al checkout
                    </a>

                    <!-- MINI CART DROPDOWN -->
                    <div
                        id="miniCartPanel"
                        class="hidden absolute right-0 top-[calc(100%+14px)] w-[380px] max-w-[calc(100vw-2rem)]
                        rounded-[32px] bg-white/95 backdrop-blur-xl border border-white
                        shadow-[0_30px_80px_rgba(122,97,112,.25)]
                        overflow-hidden origin-top-right scale-95 opacity-0 transition-all duration-200">

                        <!-- HEADER PREMIUM -->
                        <div class="px-6 py-5 bg-gradient-to-r from-bbpink/30 via-white to-bbblue/30 border-b border-white/60">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs uppercase tracking-[.2em] text-bbstrong/70 font-bold">
                                        Tu selección
                                    </div>
                                    <div class="text-lg font-black text-bbstrong mt-1">
                                        Carrito
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-bbtext/60">Total</div>
                                    <div class="text-2xl font-black text-bbstrong">
                                        S/ <?php echo number_format($resumen['total'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ITEMS -->
                        <div class="max-h-[360px] overflow-y-auto px-5 py-4 space-y-3">

                            <?php if (empty($_SESSION['carrito'])): ?>
                                <div class="rounded-[24px] border border-dashed border-bbrose bg-bbpink/15 p-6 text-center">
                                    <div class="text-base font-bold text-bbstrong">Tu carrito está vacío</div>
                                    <p class="mt-1 text-sm text-bbtext/75">
                                        Agrega productos para verlos aquí.
                                    </p>
                                </div>
                            <?php else: ?>

                                <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
                                    <?php if (isset($productos[$id])): ?>

                                        <div class="group rounded-[22px] bg-bbcream border border-bbpink/20 p-3 flex items-center gap-3 hover:shadow-md transition">

                                            <img
                                                src="<?php echo h($productos[$id]['imagen']); ?>"
                                                class="w-16 h-16 rounded-2xl object-cover"
                                                alt="<?php echo h($productos[$id]['nombre']); ?>">

                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-extrabold text-bbstrong truncate">
                                                    <?php echo h($productos[$id]['nombre']); ?>
                                                </div>

                                                <div class="text-xs text-bbtext/70 mt-1">
                                                    Cantidad: <?php echo (int)$cantidad; ?>
                                                </div>

                                                <div class="text-sm font-black text-bbstrong mt-1">
                                                    S/ <?php echo number_format((float)$productos[$id]['precio'] * (int)$cantidad, 2); ?>
                                                </div>
                                            </div>

                                            <div class="inline-flex items-center rounded-full border border-bbpink/40 bg-white shadow-soft overflow-hidden shrink-0">
                                                <form method="POST">
                                                    <input type="hidden" name="accion" value="quitar">
                                                    <input type="hidden" name="producto_id" value="<?php echo (int)$id; ?>">
                                                    <button
                                                        type="submit"
                                                        class="w-9 h-9 flex items-center justify-center text-base font-bold text-bbstrong hover:bg-bbpink/20 transition"
                                                        aria-label="Quitar uno">
                                                        −
                                                    </button>
                                                </form>

                                                <div class="min-w-[34px] h-9 flex items-center justify-center text-xs font-black text-bbstrong px-2">
                                                    <?php echo (int)$cantidad; ?>
                                                </div>

                                                <?php
                                                $disponibleMiniCart = stockDisponible((int)$id, $productos);
                                                ?>
                                                <?php if ($disponibleMiniCart > 0): ?>
                                                    <form method="POST">
                                                        <input type="hidden" name="accion" value="agregar">
                                                        <input type="hidden" name="producto_id" value="<?php echo (int)$id; ?>">
                                                        <button
                                                            type="submit"
                                                            class="w-9 h-9 flex items-center justify-center text-base font-bold text-white bg-bbstrong hover:opacity-90 transition"
                                                            aria-label="Agregar uno">
                                                            +
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button
                                                        type="button"
                                                        class="w-9 h-9 flex items-center justify-center text-base font-bold text-slate-300 bg-slate-100 cursor-not-allowed"
                                                        disabled>
                                                        +
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                        </div>

                                    <?php endif; ?>
                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                        <!-- FOOTER PREMIUM -->
                        <div class="px-5 pb-5 pt-3 border-t border-white/70 bg-white">

                            <!-- TOTAL GRANDE -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-bbtext/70">Total regalo</span>
                                <span class="text-2xl font-black text-bbstrong">
                                    S/ <?php echo number_format($resumen['total'], 2); ?>
                                </span>
                            </div>

                            <!-- BOTONES -->
                            <div class="grid grid-cols-2 gap-3">

                                <form method="POST">
                                    <input type="hidden" name="accion" value="vaciar_carrito">
                                    <button
                                        class="w-full rounded-full border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 font-semibold"
                                        <?php echo empty($_SESSION['carrito']) ? 'disabled' : ''; ?>>
                                        Vaciar
                                    </button>
                                </form>

                                <a
                                    href="checkout.php"
                                    class="w-full text-center rounded-full bg-bbstrong text-white px-4 py-3 font-bold shadow-lg hover:scale-[1.02] transition">
                                    Continuar →
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 md:px-6 lg:px-8">

        <!-- HERO -->
        <section class="relative overflow-hidden rounded-[32px] bg-white/85 backdrop-blur-xl shadow-soft border border-white/70 p-6 md:p-10">
            <div class="absolute -top-8 -left-8 w-36 h-36 rounded-full bg-bbpink/60 blur-3xl"></div>
            <div class="absolute -bottom-8 -right-8 w-36 h-36 rounded-full bg-bbblue/70 blur-3xl"></div>

            <div class="relative grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <span class="inline-flex items-center rounded-full bg-bbblue px-4 py-1.5 text-sm font-semibold text-bbstrong">
                        Regala con amor
                    </span>

                    <h2 class="mt-4 text-4xl md:text-5xl font-black tracking-tight text-bbstrong leading-tight">
                        Elige un detalle especial para nuestro bebé
                    </h2>

                    <p class="mt-4 text-base md:text-lg leading-7 text-bbtext/90 max-w-xl">
                        Agrega productos al carrito y continúa en una página aparte para completar
                        el pago con <strong>Yape</strong> o <strong>Plin</strong>.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#productos" class="rounded-full bg-bbstrong text-white px-6 py-3 font-semibold shadow-soft">
                            Ver regalos
                        </a>
                        <a href="checkout.php" class="rounded-full bg-white border border-bbrose px-6 py-3 font-semibold text-bbstrong">
                            Ver carrito
                        </a>
                    </div>
                </div>

                <div>
                    <div class="rounded-[28px] bg-gradient-to-br from-bbpink via-white to-bbblue p-4 shadow-card">
                        <img
                            src="https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=1200&q=80"
                            alt="Baby Shower"
                            class="w-full h-[340px] object-cover rounded-[24px]">
                    </div>
                </div>
            </div>
        </section>

        <?php if ($mensaje !== ''): ?>
            <div class="mt-5 rounded-2xl px-5 py-4 shadow-soft border <?php echo $tipoMensaje === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?>">
                <?php echo h($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- RESUMEN MÓVIL -->
        <section class="mt-6 md:hidden">
            <div class="rounded-[24px] bg-white/90 border border-white shadow-soft p-5 flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[.18em] text-bbstrong/70 font-semibold">Carrito</div>
                    <div class="mt-1 text-lg font-black text-bbstrong">
                        <?php echo $resumen['items']; ?> producto(s)
                    </div>
                    <div class="text-sm text-bbtext/75">
                        Total: S/ <?php echo number_format($resumen['total'], 2); ?>
                    </div>
                </div>
                <a href="checkout.php" class="rounded-full bg-bbstrong text-white px-4 py-2.5 font-semibold">
                    Ver
                </a>
            </div>
        </section>

        <!-- PRODUCTOS -->
        <section id="productos" class="mt-10">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-bbstrong">Lista de regalos</h2>
                    <p class="text-bbtext/80 mt-1">Selecciona uno o varios productos para regalar.</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($productos as $producto): ?>
                    <?php
                    $reservados = cantidadReservadaGlobal((int)$producto['id']);
                    $enCarritoProducto = (int)($_SESSION['carrito'][$producto['id']] ?? 0);
                    $disponible = max(0, (int)$producto['stock'] - $reservados - $enCarritoProducto);
                    $agotado = $disponible <= 0;
                    ?>
                    <article class="rounded-[28px] bg-white/90 border border-white shadow-card overflow-hidden hover:-translate-y-1 transition">
                        <div class="relative">
                            <img src="<?php echo h($producto['imagen']); ?>" alt="<?php echo h($producto['nombre']); ?>" class="w-full h-64 object-cover">

                            <div class="absolute top-4 left-4 flex gap-2 flex-wrap">
                                <span class="rounded-full bg-white/90 backdrop-blur px-3 py-1 text-xs font-bold text-bbstrong">
                                    <?php echo h($producto['categoria']); ?>
                                </span>

                                <?php if ($agotado): ?>
                                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                        Agotado
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                        Disponible: <?php echo $disponible; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-xl font-extrabold text-bbstrong leading-tight">
                                    <?php echo h($producto['nombre']); ?>
                                </h3>

                                <div class="text-right shrink-0">
                                    <div class="text-xs text-bbtext/70">Regalo</div>
                                    <div class="text-xl font-black text-bbstrong">
                                        S/ <?php echo number_format((float)$producto['precio'], 2); ?>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-bbtext/85">
                                <?php echo h($producto['descripcion']); ?>
                            </p>

                            <div class="mt-5 flex items-center justify-between gap-3">
                                <div class="text-sm text-bbtext/70">
                                    <?php if ($enCarritoProducto > 0): ?>
                                        Ya agregaste <strong><?php echo $enCarritoProducto; ?></strong>
                                    <?php else: ?>
                                        Listo para regalar
                                    <?php endif; ?>
                                </div>

                                <?php if ($agotado): ?>
                                    <button
                                        type="button"
                                        class="rounded-full bg-slate-200 text-slate-500 px-5 py-2.5 font-semibold cursor-not-allowed">
                                        No disponible
                                    </button>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="producto_id" value="<?php echo (int)$producto['id']; ?>">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 rounded-full bg-bbstrong text-white px-5 py-2.5 font-semibold shadow-soft hover:opacity-90 transition">
                                            <span><?php echo $enCarritoProducto > 0 ? 'Regalar otro' : 'Regalar'; ?></span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('miniCartToggle');
            const panel = document.getElementById('miniCartPanel');

            if (!toggle || !panel) return;

            function openCart() {
                panel.classList.remove('hidden');
                setTimeout(() => {
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function closeCart() {
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    panel.classList.add('hidden');
                }, 200);
            }

            toggle.addEventListener('click', function(e) {
                e.stopPropagation();

                if (panel.classList.contains('hidden')) {
                    openCart();
                } else {
                    closeCart();
                }
            });

            panel.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            document.addEventListener('click', closeCart);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeCart();
            });
        });
    </script>
</body>

</html>