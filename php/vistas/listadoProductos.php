<div class="fondo"></div>
<div class="contenedorListaProductos">
    <ion-icon name="close-circle-outline" id="btnCerrarLista"></ion-icon>
    <div class="contenedorTablaProductos">
        <table id="listadoProductos">
            <thead>
                <tr>
                    <th scope="col" style="width: 14%">Código</th>
                    <th scope="col" style="width: 24%">Descripción</th>
                    <th scope="col" style="width: 16%">Color</th>
                    <th scope="col" style="width: 10%">Talla</th>
                    <th scope="col" style="width: 12%">Cantidad</th>
                    <th scope="col" style="width: 12%">Precio</th>
                    <th scope="col" style="width: 12%">Acciones</th>
                </tr>
            </thead>
            <tbody id="datosLista"></tbody>
        </table>
    </div>
    <div class="opcionesEdicion">
        <button type="button" class="botonesEdicion" id="btnEditar">Editar</button>
        <button type="button" class="botonesEdicion uno" id="btnGuardarCambios">Guardar Cambios</button>
    </div>
</div>