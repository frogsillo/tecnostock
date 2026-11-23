# TecnoStock - Sistema de Inventario

Sistema web para la gestión de inventario de accesorios tecnológicos. Permite registrar, consultar, actualizar y desactivar productos, además de controlar las entradas y salidas de stock.

## 📌 Contexto del Problema
El emprendimiento TecnoStock comercializa accesorios tecnológicos. Anteriormente, registraba sus productos en una planilla de cálculo, lo que provocaba códigos duplicados, errores en las cantidades disponibles y poca claridad respecto del stock real. Esta aplicación web centraliza la información y mantiene actualizado el catálogo.

## 🎯 Alcance y Actor
- **Actor principal:** Encargado de inventario (único rol del sistema).
- **Alcance:** Inicio de sesión, gestión completa de categorías y productos (CRUD), control de movimientos (entradas/salidas) y alertas visuales de stock bajo.
- **Exclusiones:** No incluye módulo de ventas, pasarelas de pago reales, multibodega ni múltiples roles de usuario.

## 🛠️ Tecnologías Utilizadas
* **Backend:** PHP 8+ (usando PDO para consultas preparadas y transacciones).
* **Base de Datos:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3, Bootstrap 5, Bootstrap Icons, JavaScript vanilla.
* **Servidor Local:** XAMPP.
* **Control de Versiones:** Git y GitHub.

## 📜 Reglas de Negocio Implementadas
El sistema cumple estrictamente con las 6 reglas exigidas:
* **RN01:** El código del producto no puede repetirse (validado en PHP y con restricción `UNIQUE` en MySQL).
* **RN02:** El precio y los stocks no pueden ser negativos (validado con `CHECK` en MySQL y PHP).
* **RN03:** Todo movimiento registra fecha/hora, tipo, cantidad, producto y el usuario de la sesión.
* **RN04:** Una salida no puede dejar el stock bajo cero (validado previamente y con transacciones).
* **RN05:** Un producto con movimientos no se elimina físicamente, solo se desactiva (baja lógica).
* **RN06:** El stock se actualiza automáticamente después de cada movimiento usando transacciones PDO.

## ⚙️ Requisitos e Instalación
1. Tener instalado XAMPP (con Apache y MySQL corriendo).
2. Clonar el repositorio en la carpeta de tu servidor (ej. `/opt/lampp/htdocs/`):
   ```bash
   git clone https://github.com/frogsillo/tecnostock.git
