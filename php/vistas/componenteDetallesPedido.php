<div class="contenedorDetalles"></div>
<div class="detallesPedido">
    <ion-icon name="close-circle-outline" id="iconoCerrarLista"></ion-icon>
    <h2 id="datoNoPedido"></h2>
    <table id="datosGenerales" class="tablaDatosGenerales" border="1">
        <thead>
            <tr>
                <th>Periodo</th>
                <th>Fecha de Registro</th>
                <th>Fecha de Entrega</th>
                <th>Tipo de Venta</th>
                <th>Canal de Venta</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody id="datosGeneralesBody"></tbody>
    </table>
    <div class="contenidoPedido">
        <div class="contenedorDatosCliente">
            <h3 class="tituloContenido">Datos del Cliente</h3>
            <div class="datosCliente">
                <h3>Cliente:</h3>
                <p id="datoNombre"></p>
            </div>
            <div class="datosCliente">
                <h3>Correo:</h3>
                <p id="datoCorreo"></p>
            </div>
            <div class="datosCliente">
                <h3>Telefono:</h3>
                <p id="datoTelefono"></p>
            </div>
            <div class="datosCliente">
                <h3>Perfil de Usuario:</h3>
                <p id="datoPerfil"></p>
            </div>
            <div class="datosCliente">
                <h3>NIT:</h3>
                <p id="datoNit">/p>
            </div>
            <div class="datosCliente">
                <h3>Dirección:</h3>
                <p id="datoDireccion"></p>
            </div>
            <div class="datosCliente">
                <h3>Indicaciones:</h3>
                <p id="datoIndicaciones"></p>
            </div>
        </div>
        <div class="contenedorDatosEnvio">
            <h3 class="tituloContenido">Datos de Envío</h3>
            <div class="datosEnvio">
                <h3>Mensajería:</h3>
                <p id="datoMensajeria"></p>
            </div>
            <div class="datosEnvio">
                <h3>No. de Guía:</h3>
                <p id="datoNoGuia"></p>
            </div>
            <div class="datosEnvio">
                <h3>Costo de Envío:</h3>
                <p id="datoCostoEnvio"></p>
            </div>
            <div class="datosEnvio">
                <h3>Forma de Pago:</h3>
                <p id="datoFormaPago"></p>
            </div>
        </div>
    </div>
    <table class="tablaDetallesPedido" id="productosPedido" border="1">
        <thead>
            <tr>
                <th>Código Producto</th>
                <th>Descripción</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody id="datosProducto"></tbody>
        <tfoot>
            <tr>
                <td colspan="6" id="filaTotal">Total más envío</td>
                <td id="totalPedido"></td>
            </tr>
        </tfoot>
    </table>
</div>
<div class="actualizarPedido">
    <ion-icon name="close-circle-outline" id="iconoCerrarActualizar"></ion-icon>
    <h2 id="tituloPedidoActualizar">Detalles del Pedido No</h2>
    <div class="seccionesPedido">
        <button class="btnPedidos" id="datosPedidos">Datos de Pedido</button>
        <button class="btnPedidos" id="datosProductos">
            Productos Solicitados
        </button>
        <button class="btnPedidos" id="datosCliente">Datos del Cliente</button>
        <button class="btnPedidos" id="datosEnvio">Datos de Envío</button>
    </div>
    <div class="contenedorPrincipal Pedido">
        <div class="contenedorDatos">
            <h3>Periodo</h3>
            <input type="text" name="periodo" id="datoPeriodo" />
        </div>
        <div class="contenedorDatos">
            <h3>Fecha de Pedido</h3>
            <input type="date" name="fechaPedido" id="datoFechaPedido">
        </div>
        <div class="contenedorDatos">
            <h3>Fecha de Entrega</h3>
            <input type="date" name="datoFechaEntrega" id="datoFechaEntrega" />
        </div>
        <div class="contenedorDatos">
            <h3>Tipo de Venta</h3>
            <input type="text" name="tipoVenta" id="datoTipoVenta" />
        </div>
        <div class="contenedorDatos">
            <h3>Canal de Venta</h3>
            <input type="text" name="canalVenta" id="datoCanalVenta" />
        </div>
        <div class="contenedorDatos">
            <h3>Vendedor</h3>
            <input type="text" name="vendedor" id="datoVendedor" />
        </div>
        <div class="contenedorDatos">
            <h3>Estado del Pedido</h3>
            <select name="estadoPedido" id="estadoPedido" required>
              <option value="Pendiente">Pendiente</option>
              <option value="Entregado">Entregado</option>
              <option value="Cancelado">Cancelado</option>
            </select>
        </div>
    </div>
    <div class="contenedorPrincipal Cliente">
        <div class="contenedorDatos">
            <h3>Nombre</h3>
            <input type="text" name="nombre" id="datoNombreCliente" />
        </div>
        <div class="contenedorDatos">
            <h3>Correo</h3>
            <input type="text" name="correo" id="datoCorreoCliente" />
        </div>
        <div class="contenedorDatos">
            <h3>Perfil de Usuario</h3>
            <input type="text" name="perfilUsuario" id="datoPerfilUsuario" />
        </div>
        <div class="contenedorDatos">
            <h3>Teléfono</h3>
            <input type="text" name="telefono" id="datoTelefonoCliente" />
        </div>
        <div class="contenedorDatos">
            <h3>NIT</h3>
            <input type="text" name="nit" id="datoNITCliente" />
        </div>
    </div>
    <div class="contenedorPrincipal Envio">
        <div class="contenedorDatos">
            <h3>Dirección</h3>
            <input type="text" name="direccion" id="datoDireccionCompleta" />
        </div>
        <div class="contenedorDatos">
            <h3>Departamento</h3>
            <input type="text" name="departamento" id="datoDepartamento" />
        </div>
        <div class="contenedorDatos">
            <h3>Indicaciones</h3>
            <input type="text" name="indicacion" id="datoIndicacion" />
        </div>
        <div class="contenedorDatos">
            <h3>Mensajería</h3>
            <input type="text" name="mensajeria" id="Mensajeria" />
        </div>
        <div class="contenedorDatos">
            <h3>No. de Guía</h3>
            <input type="text" name="noGuia" id="NoGuia" />
        </div>
        <div class="contenedorDatos">
            <h3>Costo de Envío</h3>
            <input type="text" name="costoEnvio" id="CostoEnvio" />
        </div>
        <div class="contenedorDatos">
            <h3>Forma de Pago</h3>
            <input type="text" name="formaPago" id="FormaPago" />
        </div>
    </div>
    <div class="contenedorPrincipal Productos">
        <table class="tablaDetallesPedido" id="tablaProductos" border="1">
            <thead>
                <tr>
                    <th>Código Producto</th>
                    <th>Descripción</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody id="tablaDatosProductos"></tbody>
            <tfoot>
                <tr>
                    <td colspan="6" id="filaTotal">Total más envío</td>
                    <td id="totalPedidoRegistrado"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <button type="button" class="btnActualizarDatos" id="actualizarDatos">Actualizar Datos </button>
</div>
<div class="contenedorEliminar">
    <p>¿Estas seguro de eliminar el Pedido No. 
        <span id="codigoPedido"></span>
        ?
    </p>
    <div class="botonesEliminacion">
        <button class="btnEliminacion" id="confirmarEliminacion">Confirmo</button>
        <button class="btnEliminacion" id="cancelarEliminacion">Cancelar</button>
    </div>
</div>