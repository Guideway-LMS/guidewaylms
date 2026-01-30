#!/bin/bash

# Este script configura o Apache para permitir acesso ao diretório do projeto

CONF_FILE="/etc/apache2/conf-available/guideway-local.conf"
PROJECT_DIR="/home/acion2/public_html/guidewaylms/"

echo "Criando configuração do Apache em $CONF_FILE..."

# Cria o arquivo de configuração temporário
cat <<EOF > /tmp/guideway-local.conf
<Directory $PROJECT_DIR>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF

# Move protejido pelo sudo (se script rodar como root)
if [ "$EUID" -ne 0 ]; then 
  echo "Por favor, execute este script com sudo:"
  echo "sudo bash $0"
  exit 1
fi

mv /tmp/guideway-local.conf "$CONF_FILE"
chown root:root "$CONF_FILE"
chmod 644 "$CONF_FILE"

echo "Habilitando a configuração..."
a2enconf guideway-local

echo "Recarregando Apache..."
systemctl reload apache2

echo "Concluído! O acesso deve estar normalizado."
