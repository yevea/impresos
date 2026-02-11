# Plugin de Plantilla de Factura Proforma / Presupuesto 

Plugin para la versión de FacturaScripts 2017.926  
PHP 8.4.17

Simple plugin que reemplaza el documento simple de la plantilla para imprimir el Presupuesto/Factura Proforma con una plantilla de impresión en formato que funciona así:

En página de Presupuesto `/index.php?page=ventas_presupuesto` click botón **"Imprimir"** > aparece ventana de diálogo **"Imprimir factura proforma"** donde puedo elegir entre:

- Factura proforma simple
- Factura proforma de plantilla
- Maquetar

Elijo: **"Factura proforma de plantilla"** y se genera una factura proforma de plantilla en la cual, a diferencia de la Factura proforma simple, muestra Dto. Neto IVA Total como últimas líneas de la tabla de Ref. Descripción etc. arriba en vez de abajo de la página. Esto es crucial porque los clientes ya no suelen imprimir los presupuestos y cuando reciben el PDF no hacen scroll hasta abajo de la página, lo que causa errores en el pago ya que se les olvida pagar el IVA.

## Requisitos

- [FacturaScripts](https://www.facturascripts.com/) versión **2017.926** o superior
- PHP **7.0** o superior
- Plugin **facturacion_base** instalado y activado en FacturaScripts

## Instalación

### Opción 1: Instalación manual (recomendada)

1. Descarga o clona este repositorio:
   ```bash
   git clone https://github.com/yevea/tablero4.git proforma_plantilla
   ```
2. Copia la carpeta `proforma_plantilla` dentro del directorio `plugins/` de tu instalación de FacturaScripts:
   ```
   facturascripts/
   └── plugins/
       └── proforma_plantilla/
           ├── facturascripts.ini
           ├── controller/
           │   └── proforma_plantilla.php
           └── view/
               └── proforma_plantilla.html
   ```
3. En el panel de administración de FacturaScripts, ve a **Administración > Plugins**.
4. Busca **proforma_plantilla** en la lista y haz clic en **Activar**.

### Opción 2: Instalación mediante archivo ZIP

1. Descarga este repositorio como archivo ZIP (botón **Code > Download ZIP** en GitHub).
2. En el panel de administración de FacturaScripts, ve a **Administración > Plugins**.
3. Haz clic en **Subir plugin** y selecciona el archivo ZIP descargado.
4. Una vez subido, haz clic en **Activar** junto al plugin **proforma_plantilla**.

## Uso

1. Ve a **Ventas > Presupuestos** (`/index.php?page=ventas_presupuesto`).
2. Abre un presupuesto existente.
3. Haz clic en el botón **"Imprimir"**.
4. Selecciona **"Factura proforma de plantilla"** en la ventana de diálogo.
5. Se generará un PDF con los totales (Dto., Neto, IVA, Total) integrados dentro de la tabla de líneas.

## Licencia

Este plugin se distribuye bajo la licencia [GNU Lesser General Public License v3](https://www.gnu.org/licenses/lgpl-3.0.html) o posterior.

