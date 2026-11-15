/*
 * tablas.js: ordenar, filtrar y paginar las tablas del panel (solo jQuery).
 *
 * Uso:
 *   <table id="tablaX" class="tabla-datos" data-pagina="12"> con <th data-orden="texto|numero|fecha">
 *   Controles de filtro (en cualquier parte de la pagina) con data-tabla="#tablaX":
 *     - input de texto: busca en todas las columnas.
 *     - select con data-col="2": la columna 2 (desde 0) debe coincidir con el valor elegido.
 *     - input date con data-col y data-rango="desde|hasta": compara contra data-valor="AAAA-MM-DD ..." de la celda.
 *     - boton .limpiar-filtros con data-tabla: reinicia los filtros.
 *   Una celda puede traer data-valor para ordenar o filtrar por un valor distinto al texto mostrado.
 */
$(function () {

    function valorCelda($td) {
        var v = $td.attr('data-valor');
        return (v !== undefined ? v : $td.text()).toString().trim();
    }

    $('table.tabla-datos').each(function () {
        var $tabla = $(this);
        var id = '#' + this.id;
        var $cuerpo = $tabla.children('tbody');
        var porPagina = parseInt($tabla.data('pagina'), 10) || 12;
        var filas = $cuerpo.children('tr').filter(function () { return $(this).children('td[colspan]').length === 0; }).toArray();
        var orden = { col: -1, dir: 1 };
        var pagina = 1;

        /* Encabezados ordenables */
        $tabla.find('thead th[data-orden]').each(function () {
            $(this).addClass('ordenable').attr('tabindex', 0).append(' <i class="ti ti-arrows-sort ord"></i>');
        });

        /* Pie con informacion y paginas */
        var $pie = $('<div class="tabla-pie"><span class="tabla-info"></span><div class="tabla-paginas"></div></div>');
        $tabla.closest('.l-card').append($pie);

        function controles() { return $('.ft[data-tabla="' + id + '"]'); }

        function pasaFiltros(fila) {
            var $fila = $(fila), pasa = true;
            controles().each(function () {
                var $c = $(this), v = ($c.val() || '').toString().trim();
                if (v === '') { return; }
                var col = $c.data('col');

                if (col === undefined) {
                    if ($fila.text().toLowerCase().indexOf(v.toLowerCase()) === -1) { pasa = false; }
                    return;
                }
                var celda = valorCelda($fila.children('td').eq(col));
                var rango = $c.data('rango');
                if (rango === 'desde') { if (celda.slice(0, 10) < v) { pasa = false; } }
                else if (rango === 'hasta') { if (celda.slice(0, 10) > v) { pasa = false; } }
                else if (celda !== v) { pasa = false; }
            });
            return pasa;
        }

        function clave(fila, col, tipo) {
            var v = valorCelda($(fila).children('td').eq(col));
            if (tipo === 'numero') { return parseFloat(v.replace(/[^0-9.\-]/g, '').replace(/^\.$/, '')) || 0; }
            return v.toLowerCase();
        }

        function dibujar() {
            /* 1) ordenar (estable) */
            if (orden.col >= 0) {
                var tipo = $tabla.find('thead th').eq(orden.col).data('orden');
                filas = filas.map(function (f, i) { return { f: f, i: i }; }).sort(function (a, b) {
                    var ka = clave(a.f, orden.col, tipo), kb = clave(b.f, orden.col, tipo);
                    var r = (typeof ka === 'number') ? ka - kb : ka.localeCompare(kb, 'es', { numeric: true });
                    return r !== 0 ? r * orden.dir : a.i - b.i;
                }).map(function (o) { return o.f; });
                $cuerpo.append(filas);
            }

            /* 2) filtrar y paginar */
            var visibles = filas.filter(pasaFiltros);
            var paginas = Math.max(1, Math.ceil(visibles.length / porPagina));
            if (pagina > paginas) { pagina = paginas; }
            var desde = (pagina - 1) * porPagina, hasta = desde + porPagina;
            var mostrar = visibles.slice(desde, hasta);

            $(filas).addClass('fila-oculta');
            $(mostrar).removeClass('fila-oculta');

            $cuerpo.children('tr.sin-resultados').remove();
            if (visibles.length === 0 && filas.length > 0) {
                var columnas = $tabla.find('thead th').length;
                $cuerpo.append('<tr class="sin-resultados"><td colspan="' + columnas + '" class="text-center text-secondary py-5"><i class="ti ti-filter-off fs-3 d-block mb-2"></i>No hay resultados con esos filtros.</td></tr>');
            }

            /* 3) indicador de orden */
            $tabla.find('thead th.ordenable').each(function (i) {
                var $i = $(this).find('i.ord');
                var activo = $tabla.find('thead th').index(this) === orden.col;
                $(this).toggleClass('orden-activo', activo);
                $i.attr('class', 'ti ord ' + (activo ? (orden.dir === 1 ? 'ti-sort-ascending' : 'ti-sort-descending') : 'ti-arrows-sort'));
            });

            /* 4) pie (sin filas de datos no hay nada que contar) */
            $pie.toggle(filas.length > 0);
            $pie.find('.tabla-info').text(visibles.length === 0 ? '0 resultados' :
                'Mostrando ' + (desde + 1) + ' a ' + Math.min(hasta, visibles.length) + ' de ' + visibles.length +
                (visibles.length !== filas.length ? ' (filtrado de ' + filas.length + ')' : ''));
            var $pag = $pie.find('.tabla-paginas').empty();
            if (paginas > 1) {
                var boton = function (html, destino, activo, deshabilitado) {
                    return $('<button type="button" class="btn btn-sm"></button>').html(html)
                        .toggleClass('btn-primary', !!activo).toggleClass('btn-outline-secondary', !activo)
                        .prop('disabled', !!deshabilitado).data('pagina', destino);
                };
                $pag.append(boton('<i class="ti ti-chevron-left"></i>', pagina - 1, false, pagina === 1));
                for (var n = 1; n <= paginas; n++) {
                    if (paginas > 7 && n !== 1 && n !== paginas && Math.abs(n - pagina) > 1) {
                        if (n === 2 || n === paginas - 1) { $pag.append('<span class="px-1 text-secondary">...</span>'); }
                        continue;
                    }
                    $pag.append(boton(n, n, n === pagina, false));
                }
                $pag.append(boton('<i class="ti ti-chevron-right"></i>', pagina + 1, false, pagina === paginas));
            }
        }

        $pie.on('click', '.tabla-paginas button', function () { pagina = $(this).data('pagina'); dibujar(); });

        $tabla.on('click keydown', 'thead th.ordenable', function (e) {
            if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) { return; }
            e.preventDefault();
            var col = $tabla.find('thead th').index(this);
            orden = { col: col, dir: orden.col === col ? -orden.dir : 1 };
            pagina = 1;
            dibujar();
        });

        $(document).on('input change', '.ft[data-tabla="' + id + '"]', function () { pagina = 1; dibujar(); });
        $(document).on('click', '.limpiar-filtros[data-tabla="' + id + '"]', function () {
            controles().val('');
            pagina = 1;
            dibujar();
        });

        dibujar();
    });
});
