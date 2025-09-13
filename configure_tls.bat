@echo off
setlocal

if "%~1"=="" (
  echo Usage: %~nx0 domain
  exit /b 1
)

set DOMAIN=%1
set BASE_DIR=%~dp0
set SSL_DIR=%BASE_DIR%certs
set CA_DIR=%USERPROFILE%\.sail\ca

set CA_KEY=%CA_DIR%\ca.key
set CA_CERT=%CA_DIR%\ca.pem
set SERVER_KEY=%SSL_DIR%\server.key
set SERVER_CSR=%SSL_DIR%\server.csr
set SERVER_CERT=%SSL_DIR%\server.crt
set CONFIG_FILE=%SSL_DIR%\openssl.cnf

if not exist "%SSL_DIR%" mkdir "%SSL_DIR%"
if not exist "%CA_DIR%" mkdir "%CA_DIR%"

if exist "%SERVER_CERT%" if exist "%SERVER_KEY%" (
  echo Server cert and key already exist.
  exit /b 0
)

if not exist "%CA_KEY%" (
  openssl genrsa -out "%CA_KEY%" 2048
  set SUBJECT=/CN=Laravel Sail CA Self Signed CN/O=Laravel Sail CA Self Signed Organization/OU=Developers/emailAddress=rootcertificate@laravel.sail
  openssl req -x509 -new -nodes -key "%CA_KEY%" -sha256 -days 3650 -out "%CA_CERT%" -subj "%SUBJECT%"
  echo CA generated at %CA_CERT%
  certutil -addstore -f Root "%CA_CERT%"
)

openssl genrsa -out "%SERVER_KEY%" 2048

(
echo [req]
echo distinguished_name = req_distinguished_name
echo req_extensions = v3_req
echo prompt = no
echo [req_distinguished_name]
echo CN = %DOMAIN%
echo [v3_req]
echo keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
echo extendedKeyUsage = serverAuth
echo subjectAltName = DNS:%DOMAIN%,DNS:*.%DOMAIN%,DNS:localhost,DNS:mailpit,DNS:keycloak
) > "%CONFIG_FILE%"

openssl req -new -key "%SERVER_KEY%" -out "%SERVER_CSR%" -config "%CONFIG_FILE%"
openssl x509 -req -in "%SERVER_CSR%" -CA "%CA_CERT%" -CAkey "%CA_KEY%" -CAcreateserial ^
  -out "%SERVER_CERT%" -days 365 -sha256 -extfile "%CONFIG_FILE%" -extensions v3_req

echo Generated TLS certificates at %SSL_DIR%
del "%SERVER_CSR%" "%CONFIG_FILE%"
