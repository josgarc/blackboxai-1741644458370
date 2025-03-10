#!/bin/bash

# Esperar a que MySQL esté listo
until mysql -u root -e "SELECT 1" >/dev/null 2>&1; do
    echo "Esperando a que MySQL esté listo..."
    sleep 1
done

# Crear la base de datos y el usuario
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS church_networks;
USE church_networks;

# Importar el esquema de la base de datos
$(cat database.sql)

# Crear usuario y asignar permisos
CREATE USER IF NOT EXISTS 'churchuser'@'localhost' IDENTIFIED BY 'church123';
GRANT ALL PRIVILEGES ON church_networks.* TO 'churchuser'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "Base de datos inicializada correctamente"
