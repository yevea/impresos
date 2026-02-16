# Impresos - Plugin de Factura Proforma con Totales Integrados

Plugin para [FacturaScripts](https://www.facturascripts.com/) 2025+.

Plugin que añade una plantilla de factura proforma para presupuestos con totales integrados en la tabla de líneas. Al exportar un presupuesto como PDF, este plugin proporciona un formato alternativo donde los totales (Dto., Neto, IVA, Total) se muestran como últimas filas dentro de la tabla de líneas, en lugar de al pie de la página.

Esto es crucial porque los clientes ya no suelen imprimir los presupuestos y cuando reciben el PDF no hacen scroll hasta abajo de la página, lo que causa errores en el pago ya que se les olvida pagar el IVA.

## Requisitos

- [FacturaScripts](https://www.facturascripts.com/) versión **2025** o superior
- PHP **8.0** o superior

## Instalación

### Opción 1: Instalación manual

1. Descarga o clona este repositorio:
   ```bash
   git clone https://github.com/yevea/impresos.git Impresos
   ```
2. Copia la carpeta `Impresos` dentro del directorio `Plugins/` de tu instalación de FacturaScripts:
   ```
   facturascripts/
   └── Plugins/
       └── Impresos/
           ├── facturascripts.ini
           ├── Init.php
           └── Lib/
               └── Export/
                   └── ProformaPlantillaPDFExport.php
   ```
3. En el panel de administración de FacturaScripts, ve a **Administración > Plugins**.
4. Busca **Impresos** en la lista y haz clic en **Activar**.

### Opción 2: Instalación mediante archivo ZIP

1. Descarga este repositorio como archivo ZIP (botón **Code > Download ZIP** en GitHub).
2. En el panel de administración de FacturaScripts, ve a **Administración > Plugins**.
3. Haz clic en **Subir plugin** y selecciona el archivo ZIP descargado.
4. Una vez subido, haz clic en **Activar** junto al plugin **Impresos**.

## Uso

1. Ve a **Ventas > Presupuestos**.
2. Abre un presupuesto existente.
3. Haz clic en el botón **Imprimir** (PDF).
4. El presupuesto se exportará con los totales (Dto., Neto, IVA, Total) integrados dentro de la tabla de líneas.

## Licencia

Este plugin se distribuye bajo la licencia [GNU Lesser General Public License v3](https://www.gnu.org/licenses/lgpl-3.0.html) o posterior.

