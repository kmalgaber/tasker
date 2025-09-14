#!/usr/bin/env bash
set -euo pipefail

source .env

DOMAIN=$APP_DOMAIN
BASE_DIR="$(cd "$(dirname "$0")" && pwd)"
SSL_DIR="$BASE_DIR/storage/app/certs"
CA_DIR="$HOME/.sail/ca"

CA_KEY="$CA_DIR/ca.key"
CA_CERT="$CA_DIR/ca.pem"
SERVER_KEY="$SSL_DIR/server.key"
SERVER_CSR="$SSL_DIR/server.csr"
SERVER_CERT="$SSL_DIR/server.crt"
CONFIG_FILE="$SSL_DIR/openssl.cnf"

mkdir -p "$SSL_DIR" "$CA_DIR"

if [[ ! -f "$CA_KEY" || ! -f "$CA_CERT" ]]; then
  openssl genrsa -out "$CA_KEY" 2048
  SUBJECT="/CN=Laravel Sail CA Self Signed CN/O=Laravel Sail CA Self Signed Organization/OU=Developers/emailAddress=rootcertificate@laravel.sail"
  openssl req -x509 -new -nodes -key "$CA_KEY" -sha256 -days 3650 -out "$CA_CERT" -subj "$SUBJECT"
  echo "CA generated and placed at $CA_CERT"
fi

case "$(uname -s)" in
Darwin)
  sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "$CA_CERT"
  ;;
Linux)
  DISTRO=$(grep ^ID= /etc/os-release | cut -d= -f2 | tr -d '"')
  if [[ "$DISTRO" =~ (ubuntu|debian) ]]; then
    sudo cp "$CA_CERT" /usr/local/share/ca-certificates/laravel_sail_ca.crt
    sudo update-ca-certificates
  elif [[ "$DISTRO" =~ (centos|fedora|rhel|rocky|almalinux) ]]; then
    sudo cp "$CA_CERT" /etc/pki/ca-trust/source/anchors/laravel_sail_ca.crt
    sudo update-ca-trust extract
  fi
  ;;
esac

if [[ -f "$SERVER_CERT" && -f "$SERVER_KEY" ]]; then
  echo "Server cert and key already exist at "$SSL_DIR
  exit 0
fi

openssl genrsa -out "$SERVER_KEY" 2048

SAN="DNS:$DOMAIN,DNS:*.$DOMAIN,DNS:localhost,DNS:mailpit,DNS:keycloak"
cat > "$CONFIG_FILE" <<EOT
[req]
distinguished_name = req_distinguished_name
req_extensions = v3_req
prompt = no

[req_distinguished_name]
CN = $DOMAIN

[v3_req]
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
extendedKeyUsage = serverAuth
subjectAltName = $SAN
EOT

openssl req -new -key "$SERVER_KEY" -out "$SERVER_CSR" -config "$CONFIG_FILE"
openssl x509 -req -in "$SERVER_CSR" -CA "$CA_CERT" -CAkey "$CA_KEY" -CAcreateserial \
  -out "$SERVER_CERT" -days 365 -sha256 -extfile "$CONFIG_FILE" -extensions v3_req

echo "Generated TLS certificates at $SSL_DIR"
rm -f "$SERVER_CSR" "$CONFIG_FILE"
