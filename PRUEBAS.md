# Documento de Pruebas - TecnoStock

Este documento detalla las pruebas funcionales realizadas al sistema para verificar el cumplimiento de las reglas de negocio y el correcto funcionamiento del MVP.

## Tabla de Casos de Prueba

| ID | Funcionalidad | Precondición | Pasos / Datos | Resultado Esperado | Resultado Obtenido | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **P01** | Login correcto | Usuario activo en BD | Ingresar `encargado@tecnostock.com` y clave `password` | Redirección al Dashboard y sesión iniciada | Redirige correctamente al Dashboard. | Aprobado |
| **P02** | Login incorrecto | Usuario activo en BD | Ingresar correo correcto y clave `123456` | Mensaje de error "Credenciales inválidas" | Muestra el cartel rojo de error. | Aprobado |
| **P03** | Usuario desactivado | Usuario con `estado = 0` | Intentar iniciar sesión con un usuario inactivo | Mensaje "Tu usuario está desactivado" | Bloquea el acceso correctamente. | Aprobado |
| **P04** | Protección de rutas | Ninguna (Sin sesión) | Entrar directo a `dashboard/index.php` sin loguearse | Redirección forzada al Login | Rebotar al login inmediatamente. | Aprobado |
| **P05** | Creación de producto | Sesión activa | Crear producto código `TEST-01`, precio `100`, stock `5` | Producto guardado y visible en la tabla | Se guarda y muestra en el listado. | Aprobado |
| **P06** | Código duplicado (RN01) | Producto `TEST-01` existe | Intentar crear otro producto con código `TEST-01` | Mensaje de error "El código ya existe" | Bloquea la creación con cartel rojo. | Aprobado |
| **P07** | Valores negativos (RN02) | Sesión activa | Intentar crear producto con precio `-50` o stock `-1` | El sistema impide guardar valores negativos | Validación HTML y PHP bloquean el envío. | Aprobado |
| **P08** | Edición de producto | Producto existente | Modificar nombre y precio del producto `TEST-01` | Datos actualizados en la tabla | Los datos se actualizan correctamente. | Aprobado |
| **P09** | Búsqueda de productos | Múltiples productos | Buscar la palabra "Cable" en el buscador | Tabla muestra solo los productos coincidentes | Filtra por nombre, código o categoría. | Aprobado |
| **P10** | Desactivación de producto | Producto activo | Hacer clic en el botón rojo (basura) del producto | Estado cambia a "Inactivo", no se borra físicamente | Cambia a gris y desaparece botón de basura. | Aprobado |
| **P11** | Entrada de stock (RN06) | Producto con stock 5 | Registrar entrada de 10 unidades | Stock actual se actualiza a 15 automáticamente | Suma correctamente y crea el movimiento. | Aprobado |
| **P12** | Salida válida (RN06) | Producto con stock 15 | Registrar salida de 5 unidades | Stock actual se actualiza a 10 automáticamente | Resta correctamente y crea el movimiento. | Aprobado |
| **P13** | Salida sin stock (RN04) | Producto con stock 5 | Intentar registrar salida de 20 unidades | Error "Stock insuficiente", el stock no cambia | Bloquea la operación con alerta roja. | Aprobado |
| **P14** | Alerta de stock bajo (RN16) | Producto con stock < stock_mínimo | Entrar al listado de productos | Fila pintada de rojo y badge de advertencia | Resalta visualmente la fila. | Aprobado |
| **P15** | Categoría con productos (RN05) | Categoría con productos | Intentar desactivar la categoría "Cables" | Error "No se puede desactivar porque tiene productos" | Bloquea la desactivación correctamente. | Aprobado |
| **P16** | Integridad referencial (RN05) | Producto con movimientos | Intentar borrar producto con movimientos en PHPMyAdmin | La base de datos rechaza el borrado (FK Restrict) | Error de restricción de clave foránea. | Aprobado |

## Conclusión de las Pruebas
Todas las pruebas realizadas fueron superadas con éxito, demostrando que el sistema cumple con las funcionalidades del MVP y respeta estrictamente las 6 Reglas de Negocio (RN01 a RN06) definidas en el análisis inicial.
