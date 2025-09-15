# Introduction

This is a resource-based API server for **Tasker**, a simple task management platform, built with Laravel
to demonstrate tools and techniques used in professional product development workflows.

The API is fully compliant with *OpenAPI 3.1* standard: you can access the documentation at `https://tasker.test/docs/api`.
The page provides an option to download the specification file, so you can import it into Postman to generate the collection
or use it to generate the client code of corresponding web/mobile applications.

### Authentication
The project uses [Keycloak](#keycloak) as an *authorization server*, therefore,
the *resource server* (the Laravel application) will not store user credentials nor issue any kind of authorization token.
You need to send a bearer token issued by the authorization server in the `Authorization` header of requests to the resource server.
To obtain the token you can use [Authorization Code with PKCE](https://datatracker.ietf.org/doc/html/rfc7636) flow for the client ID `web`.
OpenID Connect Discovery document can be found at `https://tasker.test/auth/realms/sso/.well-known/openid-configuration`

---

_**DISCLOSURE:** No AI agent had been used to write or modify any content in this repository.
However, LLM platforms, such as ChatGPT and Grok was used along with search engines for
identifying some code-related issues encountered during the development as well as potential solutions for them;
discovering some features of the framework and the other libraries missing or difficult to find in their official documentation;
and helping with the translation. This disclosure will be kept updated to reflect the latest situation._

---
# Setup

## Prerequisites

### Mac

##### Requirements:

- [Homebrew](https://brew.sh) — can be installed from **Terminal** as shown below:
    ```shell
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    (echo; echo 'eval "$(/opt/homebrew/bin/brew shellenv)"') >> ~/.zprofile
    eval "$(/opt/homebrew/bin/brew shellenv)"
    ```

Open **Terminal** and run the below command to install Docker:
```shell
brew install --cask docker
```

---
### Windows

##### Requirements:

- [winget](https://apps.microsoft.com/detail/9NBLGGH4NNS1)
- [WSL (Windows Subsystem for Linux)](https://apps.microsoft.com/detail/9P9TQF7MRM4R)

##### Recommended tools:

- [Windows Terminal](https://apps.microsoft.com/detail/9N0DX20HK701)

Open **Terminal**(or **PowerShell** if you don't have it) and run the below commands:

1. **Install OpenSSL:**
   ```shell
   winget install FireDaemon.OpenSSL
   ```
2. **Install WSL and Ubuntu Distro:**
   ```shell
   wsl --install
   ```
   After restarting your PC, you'll get asked to set a username and password for Ubuntu.

   You can SSH into Ubuntu by running `ubuntu` from PowerShell. This allows running Linux commands within the distro.
   A new Ubuntu terminal profile will also be available in Windows Terminal, simplifying access.

   Ubuntu's home directory (`~`) can be accessed from Windows at `\\wsl$\Ubuntu\home\{user}`.

   **Important:** Avoid using the Windows filesystem to store projects running in Docker, as it can significantly affect container performance.

3. **Install Docker Desktop:**
   ```shell
   winget install -e --id Docker.DockerDesktop
   ```
   Enable the WSL2 engine following the instructions [here](https://docs.docker.com/desktop/wsl/#turn-on-docker-desktop-wsl-2).

**From this point onward, all commands must be run from within the Ubuntu WSL Terminal.**

---

## Installing the project

Clone the repository:
```shell
mkdir -p ~/code && cd ~/code
git clone https://github.com/elnurvl/tasker.git
cd tasker
```

*All subsequent commands should be executed from the project directory (in our case, `~/code/tasker`).*

### Environment Variables

- Add the following variables to the profile if they are missing:
  ```shell
  echo "export WWWUSER=$UID WWWGROUP=$(id -g)" >> $PROFILE && source $PROFILE && env | grep -E 'WWW'
  ```
  These variables match the container's user and group IDs with the host for consistent file permissions.


- Copy `.env.example` to `.env`:
  ```shell
  cp .env.example .env
  ```

- Create a Docker network for communication between the projects:
  ```shell
  docker network create tasker
  ```

### Installing the dependencies

1. Install the packages
    ```shell
    docker run --rm --interactive --tty --volume $PWD:/app composer --ignore-platform-reqs install
    ```
2. Create and start containers:
    ```shell
    docker compose up -d
    ```
3. Link local storage:
    ```shell
    docker compose exec -u sail laravel.test php artisan storage:link
    ```
4. Run the migrations and seed the database:
    ```shell
    docker compose exec -u sail laravel.test php artisan migrate:fresh --seed
    ```

### Configuring the site
To access the API server locally, you need to add an entry for `APP_DOMAIN` in the **hosts** file
and generate TLS certificates signed with a system-trusted CA (for HTTPS).
Both of these require elevated privileges.

```shell
chmod +x configure_site
./configure_site
docker compose up -d --force-recreate
```

After completing the above steps the API server should be available at the configured `APP_DOMAIN`.

_Note: To use a different domain, change `APP_DOMAIN` in `.env` before running the above command._

---
## Keycloak

Keycloak is an open-source Identity and Access Management (IAM) solution,
responsible for issuing authorization tokens defined by OpenID Connect protocol.

You can access the Keycloak admin console at `https://tasker.test/auth` endpoint with the following credentials:

```
username: admin
password: password
```

The project already defines a minimal realm configuration out of the box.
The realm managing the users and authorization tokens for the API application is `sso`.
It will be reset to the default configuration everytime the `keycloak` compose service is recreated.

```shell
sail up keycloak -d --force-recreate
```

**NOTE: Do not make any changes in the `master` realm unless absolutely necessary.**

#### Configurations
Realm configurations can be found in `storage/keycloak/data`.

You can export realms with:

```shell
docker compose exec keycloak bin/kc.sh export --dir data
```

**Note: `data/import` directory should be reserved for default realm configurations.**

#### Debugging
Log files can be found at `storage/logs/keycloak`.

## Mailpit

Mailpit is a mail testing tool for developers. It catches all outgoing emails and allows you to view them in a web interface.
You can access the Mailpit web interface at `https://tasker.test/mail`.

## Horizon

Horizon is a queue management system for Laravel applications.
Queues are used to index tasks in the background for full text search functionality.
You can access the dashboard at `https://tasker.test/horizon`.
