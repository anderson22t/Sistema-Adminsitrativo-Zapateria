<div class="fondo"></div>
<div class="contenedorListaProductos">
    <ion-icon name="close-circle-outline" id="btnCerrarLista"></ion-icon>
    <div class="contenedorTablaProductos">
        <table id="listadoProductos" border="1">
            <thead>
                <tr>
                    <th style="width: 17%">Código Producto</th>
                    <th style="width: 25%">Descripción</th>
                    <th style="width: 18%">Color</th>
                    <th>Talla</th>
                    <th style="width: 13%">Cantidad</th>
                    <th>Precio</th>
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