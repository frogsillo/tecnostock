# Documentación del Proyecto: TecnoStock

## 1. Descripción del Problema
TecnoStock es un negocio que comercializa accesorios tecnológicos. Actualmente, registra sus productos en una planilla manual, lo que genera:
- Códigos de producto duplicados.
- Errores constantes en las cantidades de stock.
- Falta de alertas sobre cuándo es necesario reponer mercadería.
- Ausencia de un historial de quién y cuándo movió el inventario.

## 2. Objetivos

**Objetivo General:**
Desarrollar una aplicación web local que centralice la gestión del inventario de TecnoStock, permitiendo registrar, actualizar y desactivar productos, además de controlar movimientos de entrada y salida, garantizando la integridad de los datos.

**Objetivos Específicos:**
1. Diseñar una base de datos relacional en MySQL con integridad referencial.
2. Implementar un sistema de autenticación seguro con contraseñas encriptadas.
3. Permitir la gestión completa de categorías y productos (CRUD).
4. Registrar entradas y salidas de stock actualizando automáticamente las cantidades.
5. Alertar visualmente cuando un producto esté por debajo del stock mínimo.
6. Conservar el historial de movimientos sin eliminar productos físicamente.

## 3. Alcance y Exclusiones

**El sistema SÍ incluye:**
- Inicio y cierre de sesión.
- Gestión de Categorías (Crear, listar, editar, desactivar).
- Gestión de Productos (Crear, listar, buscar, editar, desactivar).
- Búsqueda por nombre, código o categoría.
- Registro de Entradas y Salidas de stock.
- Dashboard con estadísticas y alertas de stock bajo.

**El sistema NO incluye (Exclusiones):**
- Múltiples roles de usuario (solo existe el "Encargado de Inventario").
- Módulo de ventas o facturación.
- Pasarelas de pago.
- Multibodega (el sistema maneja un solo depósito).
- Eliminación física de productos con historial.

## 4. Reglas de Negocio (RN)
El sistema aplica estrictamente las siguientes reglas:
- **RN01:** El código del producto no puede repetirse (validado en PHP y con restricción `UNIQUE` en MySQL).
- **RN02:** El precio y los stocks no pueden ser negativos (validado con `CHECK` en MySQL y PHP).
- **RN03:** Todo movimiento debe registrar fecha, tipo, cantidad, producto y usuario.
- **RN04:** Una salida no puede dejar el stock bajo cero (validado en la transacción PHP).
- **RN05:** Un producto con movimientos no se elimina físicamente, solo se cambia su estado a inactivo (baja lógica).
- **RN06:** El stock se actualiza automáticamente después de cada movimiento usando transacciones PDO.

## 5. Requerimientos Funcionales Principales
- **RF01:** El sistema debe permitir el inicio de sesión con correo y contraseña.
- **RF02:** El sistema debe permitir dar de alta productos con todos sus atributos.
- **RF03:** El sistema debe contar con un buscador dinámico para el catálogo.
- **RF04:** El sistema debe registrar entradas y salidas, afectando el stock en tiempo real.
- **RF05:** El sistema debe mostrar una alerta visual roja cuando `stock_actual < stock_minimo`.

## 6. Historias de Usuario (Ejemplos)
- *Como encargado de inventario, quiero iniciar sesión con mi correo para acceder de forma segura al sistema.*
- *Como encargado, quiero registrar nuevos productos con su código único para evitar confusiones en el catálogo.*
- *Como encargado, quiero ver una alerta roja en los productos con stock bajo para saber qué debo reponer.*
- *Como encargado, quiero registrar salidas de stock y que el sistema no me permita vender más de lo que tengo disponible.*
