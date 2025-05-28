<!DOCTYPE html>
<html>

<head>
    <title>Notificaciones | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/navbar.php";
            ?>
            <div class="col d-flex flex-column  min-vh-100 gris">
                <?php
                require_once "vista/componentes/header.php";
                ?>
                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>NOTIFICACIONES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <table id="notificaciones" class="table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>USUARIO</th>
                                            <th>ACCIÓN</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>FECHA</th>
                                            <th>LEIDA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registros as $registro): ?>
                                            <tr>
                                                <td><?php echo $registro["nombre"] ?></td>
                                                <td><?php echo $registro["titulo"] ?></td>
                                                <td><?php echo $registro["descripcion"] ?></td>
                                                <td><?php echo $registro["fecha"] ?></td>
                                                <td><?php echo ($registro["leida"] == 1) ? "SI" : "NO"; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <?php
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>

    <script>
        $('#notificaciones').DataTable({
            responsive: true,
            "scrollX": true,
            "pageLength": 10,
            "aaSorting": [],
            language: {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "infoPostFix": "",
                "search": "Buscar:",
                "url": "",
                "infoThousands": ",",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "<i class='bi bi-caret-right'></i>",
                    "previous": "<i class='bi bi-caret-left'></i>"
                },
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
            }
        });
    </script>

</body>

</html>