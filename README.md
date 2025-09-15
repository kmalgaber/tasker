# Introduction

This is a resource-based API server for **Tasker**, a simple task management platform, built with Laravel
to demonstrate tools and techniques used in professional product development workflows.

The API is fully compliant with *OpenAPI 3.1* standard: you can access the documentation at `/docs/api`.
The page provides an option to download the specification file, so you can import it into Postman to generate the collection
or use it to generate the client code of corresponding web/mobile applications.

### Authentication
The project uses [Keycloak](#keycloak) as an *authorization server*, therefore,
the *resource server* (the Laravel application) will not store user credentials nor issue any kind of authorization token.
You need to send a bearer token issued by the authorization server in the `Authorization` header of requests to the resource server.
To obtain the token you can use [Authorization Code with PKCE](https://datatracker.ietf.org/doc/html/rfc7636) flow for the client ID `web`.
OpenID Connect Discovery document can be found at `/auth/realms/sso/.well-known/openid-configuration`

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

Open **Terminal** and run the below commands:

1. Install Git Credentials Manager:
    ```shell
    brew install --cask git-credential-manager
    ```

2. Install Docker:
    ```shell
    brew install --cask docker
    ```

3. Set temporary environment variables to store the location of profile and hosts files which will be used in the later steps:
    ```shell
    PROFILE=~/.zprofile
    HOSTS=/private/etc/hosts
    ```
   _Note: This command, along with everything that follows, should be executed within a single terminal session.
   The above variables will need to be redefined if a new session is initiated._
---
### Windows

##### Requirements:

- [winget](https://apps.microsoft.com/detail/9NBLGGH4NNS1)
- [WSL (Windows Subsystem for Linux)](https://apps.microsoft.com/detail/9P9TQF7MRM4R)

##### Recommended tools:

- [Windows Terminal](https://apps.microsoft.com/detail/9N0DX20HK701)

Open **Terminal**(or **PowerShell** if you don't have it) and run the below commands:


1. **Install Git on Windows:**
   ```shell
   winget install --id Git.Git -e --source winget
   ```
   *Note: Installing Git on your host OS is necessary, even if Ubuntu distro comes with Git, to enable credential saving.*

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

4. Set temporary environment variables to store the location of profile and hosts files which will be used in the later steps:
    ```shell
    PROFILE=~/.profile
    HOSTS=/mnt/c/Windows/System32/drivers/etc/hosts
    ```
   _Note: This command, along with everything that follows, should be executed in the Ubuntu shell, within a single terminal session.
   The above variables will need to be redefined if a new session is initiated._

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

### Network configuration:
- To enable access to the web server from the browser via the configured domain,
  add an entry for `APP_DOMAIN`(in our case, `tasker.test`) in the **hosts** file:
    ```shell
    echo -e '127.0.0.1 tasker.test' | sudo tee -a $HOSTS
    ```
  _Note: If you get a permission error, try to open the $HOSTS file in an admin-privileged text editor and
  add the string in the quotes as a new line manually._

  _Note: To use a different domain, change `APP_DOMAIN` in `.env`
  before reapplying the [Configuring TLS](#configuring-tls) step._


- Create a Docker network for communication between the projects:
  ```shell
  docker network create tasker
  ```

### Installing the dependencies

1. Create and start containers:
    ```shell
    docker compose up -d
    ```

2. Install the packages:
   ```shell
   docker compose exec -u sail laravel.test composer install
   ```
3. Restart the containers:
    ```shell
    docker compose restart
    ```
4. Link local storage:
    ```shell
    docker compose exec -u sail laravel.test php artisan storage:link
    ```

### Configuring TLS
You need to generate TLS certificates signed with a system-trusted CA to access the API with HTTPS.

**Mac or Linux:**
```shell
chown +x configure_tls.sh
./configure_tls.sh
docker compose restart nginx
```

**Windows(needs to be run from PowerShell):**
```shell
./configure_tls.bat
docker compose restart nginx
```

### Database
Run the migrations and seed the database:
```shell
docker compose exec -u sail laravel.test php artisan migrate:fresh --seed
```

After completing the above steps the API is ready to use.

---
## Keycloak

Keycloak is an open-source Identity and Access Management (IAM) solution,
responsible for issuing authorization tokens defined by OpenID Connect protocol.

You can access the Keycloak admin console at `/auth` endpoint with the following credentials:

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
You can access the Mailpit web interface at `/mail`.

## Horizon

Horizon is a queue management system for Laravel applications.
Queues are used to index tasks in the background for full text search functionality.
You can access the dashboard at `/horizon`.
