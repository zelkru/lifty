$(function () {

    function valorDeCelda($celda) {
        var valorGuardado = $celda.attr('data-valor');
        return (valorGuardado !== undefined ? valorGuardado : $celda.text()).toString().trim();
    }

    $('table.tabla-datos').each(function () {
        var $tabla = $(this);
        var idTabla = '#' + this.id;
        var $cuerpo = $tabla.children('tbody');
        var filasPorPagina = parseInt($tabla.data('pagina'), 10) || 12;
        var filas = $cuerpo.children('tr').filter(function () {
            return $(this).children('td[colspan]').length === 0;
        }).toArray();
        var orden = { columna: -1, direccion: 1 };
        var paginaActual = 1;
        var $pestanas = $('.pestanas-estado[data-tabla="' + idTabla + '"]');
        var estadoMostrado = 'activo';

        $tabla.find('thead th[data-orden]').each(function () {
            $(this).addClass('ordenable').attr('tabindex', 0).append(' <i class="ti ti-arrows-sort ord"></i>');
        });

        var $pie = $('<div class="tabla-pie"><span class="tabla-info"></span><div class="tabla-paginas"></div></div>');
        $tabla.closest('.l-card').append($pie);

        function controlesDeFiltro() {
            return $('.ft[data-tabla="' + idTabla + '"]');
        }

        function esInactiva(fila) {
            var columnaEstado = $pestanas.data('col');
            return /^inactiv/i.test(valorDeCelda($(fila).children('td').eq(columnaEstado)));
        }

        function coincideConEstado(fila) {
            return $pestanas.length === 0 || esInactiva(fila) === (estadoMostrado === 'inactivo');
        }

        function cumpleFiltros(fila) {
            var $fila = $(fila);
            var cumple = true;

            controlesDeFiltro().each(function () {
                var $control = $(this);
                var textoFiltro = ($control.val() || '').toString().trim();

                if (textoFiltro === '') {
                    return;
                }

                var columna = $control.data('col');

                if (columna === undefined) {
                    if ($fila.text().toLowerCase().indexOf(textoFiltro.toLowerCase()) === -1) {
                        cumple = false;
                    }
                    return;
                }

                var valorCelda = valorDeCelda($fila.children('td').eq(columna));
                var rango = $control.data('rango');

                if (rango === 'desde') {
                    if (valorCelda.slice(0, 10) < textoFiltro) { cumple = false; }
                }
                else if (rango === 'hasta') {
                    if (valorCelda.slice(0, 10) > textoFiltro) { cumple = false; }
                }
                else if (valorCelda !== textoFiltro) {
                    cumple = false;
                }
            });

            return cumple;
        }

        function claveDeOrden(fila, columna, tipo) {
            var valorCelda = valorDeCelda($(fila).children('td').eq(columna));

            if (tipo === 'numero') {
                return parseFloat(valorCelda.replace(/[^0-9.\-]/g, '').replace(/^\.$/, '')) || 0;
            }
            return valorCelda.toLowerCase();
        }

        function ordenarFilas() {
            var tipo = $tabla.find('thead th').eq(orden.columna).data('orden');

            filas = filas.map(function (fila, posicion) {
                return { fila: fila, posicion: posicion };
            }).sort(function (primero, segundo) {
                var claveA = claveDeOrden(primero.fila, orden.columna, tipo);
                var claveB = claveDeOrden(segundo.fila, orden.columna, tipo);
                var diferencia = (typeof claveA === 'number') ? claveA - claveB : claveA.localeCompare(claveB, 'es', { numeric: true });
                return diferencia !== 0 ? diferencia * orden.direccion : primero.posicion - segundo.posicion;
            }).map(function (elemento) {
                return elemento.fila;
            });

            $cuerpo.append(filas);
        }

        function actualizarContadoresDePestanas() {
            var inactivos = filas.filter(esInactiva).length;
            $pestanas.find('button[data-estado="activo"] .badge').text(filas.length - inactivos);
            $pestanas.find('button[data-estado="inactivo"] .badge').text(inactivos);
        }

        function actualizarIconosDeOrden() {
            $tabla.find('thead th.ordenable').each(function () {
                var $icono = $(this).find('i.ord');
                var esColumnaOrdenada = $tabla.find('thead th').index(this) === orden.columna;
                var claseIcono = 'ti-arrows-sort';

                if (esColumnaOrdenada) {
                    claseIcono = orden.direccion === 1 ? 'ti-sort-ascending' : 'ti-sort-descending';
                }

                $(this).toggleClass('orden-activo', esColumnaOrdenada);
                $icono.attr('class', 'ti ord ' + claseIcono);
            });
        }

        function crearBotonDePagina(contenido, destino, esActual, deshabilitado) {
            return $('<button type="button" class="btn btn-sm"></button>').html(contenido)
                .toggleClass('btn-primary', !!esActual)
                .toggleClass('btn-outline-secondary', !esActual)
                .prop('disabled', !!deshabilitado)
                .data('pagina', destino);
        }

        function dibujarPaginacion(totalPaginas) {
            var $paginas = $pie.find('.tabla-paginas').empty();

            if (totalPaginas <= 1) {
                return;
            }

            $paginas.append(crearBotonDePagina('<i class="ti ti-chevron-left"></i>', paginaActual - 1, false, paginaActual === 1));

            for (var numero = 1; numero <= totalPaginas; numero++) {
                var estaLejos = totalPaginas > 7 && numero !== 1 && numero !== totalPaginas && Math.abs(numero - paginaActual) > 1;

                if (estaLejos) {
                    if (numero === 2 || numero === totalPaginas - 1) {
                        $paginas.append('<span class="px-1 text-secondary">...</span>');
                    }
                    continue;
                }
                $paginas.append(crearBotonDePagina(numero, numero, numero === paginaActual, false));
            }

            $paginas.append(crearBotonDePagina('<i class="ti ti-chevron-right"></i>', paginaActual + 1, false, paginaActual === totalPaginas));
        }

        function dibujar() {
            if (orden.columna >= 0) {
                ordenarFilas();
            }

            var filasDelEstado = filas.filter(coincideConEstado);
            var filasVisibles = filasDelEstado.filter(cumpleFiltros);
            var totalPaginas = Math.max(1, Math.ceil(filasVisibles.length / filasPorPagina));

            if ($pestanas.length) {
                actualizarContadoresDePestanas();
            }
            if (paginaActual > totalPaginas) {
                paginaActual = totalPaginas;
            }

            var primeraFila = (paginaActual - 1) * filasPorPagina;
            var ultimaFila = primeraFila + filasPorPagina;

            $(filas).addClass('fila-oculta');
            $(filasVisibles.slice(primeraFila, ultimaFila)).removeClass('fila-oculta');

            $cuerpo.children('tr.sin-resultados').remove();
            if (filasVisibles.length === 0 && filas.length > 0) {
                var totalColumnas = $tabla.find('thead th').length;
                $cuerpo.append('<tr class="sin-resultados"><td colspan="' + totalColumnas + '" class="text-center text-secondary py-5"><i class="ti ti-filter-off fs-3 d-block mb-2"></i>No hay resultados con esos filtros.</td></tr>');
            }

            actualizarIconosDeOrden();

            $pie.toggle(filas.length > 0);
            $pie.find('.tabla-info').text(filasVisibles.length === 0 ? '0 resultados' :
                'Mostrando ' + (primeraFila + 1) + ' a ' + Math.min(ultimaFila, filasVisibles.length) + ' de ' + filasVisibles.length +
                (filasVisibles.length !== filasDelEstado.length ? ' (filtrado de ' + filasDelEstado.length + ')' : ''));

            dibujarPaginacion(totalPaginas);
        }

        $pie.on('click', '.tabla-paginas button', function () {
            paginaActual = $(this).data('pagina');
            dibujar();
        });

        $tabla.on('click keydown', 'thead th.ordenable', function (evento) {
            var esTeclaDeActivacion = evento.which === 13 || evento.which === 32;

            if (evento.type === 'keydown' && !esTeclaDeActivacion) {
                return;
            }

            evento.preventDefault();
            var columna = $tabla.find('thead th').index(this);
            orden = { columna: columna, direccion: orden.columna === columna ? -orden.direccion : 1 };
            paginaActual = 1;
            dibujar();
        });

        $pestanas.on('click', 'button', function () {
            $pestanas.find('button').removeClass('active');
            $(this).addClass('active');
            estadoMostrado = $(this).data('estado');
            paginaActual = 1;
            dibujar();
        });

        $(document).on('input change', '.ft[data-tabla="' + idTabla + '"]', function () {
            paginaActual = 1;
            dibujar();
        });

        $(document).on('click', '.limpiar-filtros[data-tabla="' + idTabla + '"]', function () {
            controlesDeFiltro().val('');
            $pestanas.find('button').removeClass('active').filter('[data-estado="activo"]').addClass('active');
            estadoMostrado = 'activo';
            paginaActual = 1;
            dibujar();
        });

        dibujar();
    });
});
