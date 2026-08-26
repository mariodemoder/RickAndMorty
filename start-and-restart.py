import subprocess
import sys
import time
import os
import re
from datetime import date

# Config
HEALTH_TIMEOUT = 60
HEALTH_INTERVAL = 2

# Colores
GREEN = "\033[92m"
YELLOW = "\033[93m"
RED = "\033[91m"
CYAN = "\033[96m"
RESET = "\033[0m"
BOLD = "\033[1m"


def log(color, prefix, msg):
    print(f"{color}{BOLD}[{prefix}]{RESET} {msg}")


def run(cmd, capture=False):
    return subprocess.run(
        cmd, shell=True, capture_output=capture, text=True
    )


def get_project_name():
    """Derive Docker Compose project name from current directory (same as docker compose)."""
    cwd = os.path.basename(os.getcwd()).lower()
    return re.sub(r"[^a-z0-9]", "", cwd)


def get_container_names(project):
    """Return (laravel_container, mysql_container) based on project name."""
    return f"{project}-laravel.test-1", f"{project}-mysql-1"


def ensure_docker_running():
    result = run("docker info", capture=True)
    if result.returncode == 0:
        log(GREEN, "DOCKER", "Docker ya está corriendo.")
        return

    docker_desktop = os.path.expandvars(
        r"%ProgramFiles%\Docker\Docker\Docker Desktop.exe"
    )
    if not os.path.exists(docker_desktop):
        log(RED, "ERROR", f"No se encontró Docker Desktop en: {docker_desktop}")
        sys.exit(1)

    log(YELLOW, "DOCKER", "Iniciando Docker Desktop...")
    subprocess.Popen(
        [docker_desktop],
        creationflags=subprocess.DETACHED_PROCESS | subprocess.CREATE_NO_WINDOW,
    )

    log(YELLOW, "WAIT", "Esperando a que Docker esté listo...")
    start = time.time()
    while time.time() - start < HEALTH_TIMEOUT:
        result = run("docker info", capture=True)
        if result.returncode == 0:
            print()
            log(GREEN, "OK", "Docker está listo.")
            return
        print(".", end="", flush=True)
        time.sleep(HEALTH_INTERVAL)

    print()
    log(RED, "FAIL", "Timeout esperando Docker Desktop.")
    sys.exit(1)


def wait_container(container, condition, label):
    log(YELLOW, "WAIT", f"Esperando {label}...")
    fmt = "{{.State." + condition + "}}"
    cmd = f'docker inspect --format="{fmt}" {container}'
    start = time.time()
    while time.time() - start < HEALTH_TIMEOUT:
        result = run(cmd, capture=True)
        value = result.stdout.strip().strip("'\"")
        if condition == "Health.Status":
            if value == "healthy":
                log(GREEN, "OK", f"{label} está listo")
                return True
        elif condition == "Status":
            if value.startswith("running"):
                log(GREEN, "OK", f"{label} está corriendo")
                return True
        time.sleep(HEALTH_INTERVAL)
    log(RED, "FAIL", f"Timeout esperando {label}")
    return False


def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(script_dir)

    project = get_project_name()
    container_laravel, container_mysql = get_container_names(project)

    print(f"\n{CYAN}{BOLD}{'='*50}")
    print(f"  START AND RESTART - Laravel Sail")
    print(f"  Project: {project}")
    print(f"{'='*50}{RESET}\n")

    ensure_docker_running()

    # 1. docker compose down
    log(YELLOW, "STEP 1", "Deteniendo containers existentes...")
    run("docker compose down")
    time.sleep(2)

    # 2. docker compose up -d
    log(YELLOW, "STEP 2", "Levantando containers...")
    result = run("docker compose up -d")
    if result.returncode != 0:
        log(RED, "ERROR", "No se pudieron levantar los containers")
        sys.exit(1)

    # 3. Esperar MySQL healthy
    if not wait_container(container_mysql, "Health.Status", "MySQL"):
        sys.exit(1)

    # 4. Esperar Laravel corriendo
    if not wait_container(container_laravel, "Status", "Laravel"):
        sys.exit(1)

    print()
    log(GREEN, "READY", "Containers listos.\n")

    # 5. Composer install (inside container)
    log(YELLOW, "STEP 5", "Instalando dependencias PHP (composer install)...")
    result = run(f"docker exec {container_laravel} composer install --no-interaction")
    if result.returncode != 0:
        log(RED, "ERROR", "composer install falló")
        sys.exit(1)
    log(GREEN, "OK", "Dependencias PHP instaladas.\n")

    # 6. Key generate (inside container)
    log(YELLOW, "STEP 6", "Generando APP_KEY...")
    run(f"docker exec {container_laravel} php artisan key:generate --force")
    log(GREEN, "OK", "APP_KEY generada.\n")

    # 7. Migrate (inside container)
    log(YELLOW, "STEP 7", "Ejecutando migraciones...")
    result = run(f"docker exec {container_laravel} php artisan migrate --force")
    if result.returncode != 0:
        log(RED, "ERROR", "Migraciones fallaron")
        sys.exit(1)
    log(GREEN, "OK", "Migraciones ejecutadas.\n")

    # 8. Sync data from Rick & Morty API
    log(YELLOW, "STEP 8", "Despachando sync:rick-and-morty...")
    result = run(f"docker exec {container_laravel} php artisan sync:rick-and-morty")
    if result.returncode != 0:
        log(YELLOW, "WARN", "Sync dispatch falló (puede que ya esté sincronizado)")
    else:
        log(GREEN, "OK", "Sync despachado a la cola.\n")

    # 9. npm install (inside container for Vite)
    log(YELLOW, "STEP 9", "Instalando dependencias npm...")
    result = run(f"docker exec {container_laravel} npm install")
    if result.returncode != 0:
        log(RED, "ERROR", "npm install falló")
        sys.exit(1)
    log(GREEN, "OK", "Dependencias npm instaladas.\n")

    # 10. Start services in separate terminals
    log(YELLOW, "STEP 10", "Iniciando queue:work, sync logs y Vite...\n")

    # Queue worker in new terminal
    os.system(f'start "Queue Worker" cmd /k "docker exec -it {container_laravel} php artisan queue:work --sleep=1 --tries=3 --verbose"')
    time.sleep(1)

    # Sync logs tail in new terminal
    sync_log = f"storage/logs/sync-{date.today()}.log"
    os.system(f'start "Sync Logs" cmd /k "docker exec -it {container_laravel} tail -f {sync_log}"')

    # Vite dev server (foreground — Ctrl+C stops everything)
    log(GREEN, "ALL READY", "Servicios iniciados:\n")
    log(GREEN, "  ", "  - Laravel:     http://localhost:8080")
    log(GREEN, "  ", "  - Vite HMR:    http://localhost:5173")
    log(GREEN, "  ", "  - Queue Worker: corriendo en terminal separada")
    log(GREEN, "  ", "  - Sync Logs:   corriendo en terminal separada")
    print()
    log(YELLOW, "INFO", "Presiona Ctrl+C para detener todo.\n")

    vite_proc = subprocess.Popen(
        f"docker exec -it {container_laravel} npm run dev",
        shell=True,
    )

    try:
        vite_proc.wait()
    except KeyboardInterrupt:
        pass
    finally:
        vite_proc.terminate()
        vite_proc.wait()
        print()
        log(YELLOW, "STOP", "Deteniendo containers...")
        run("docker compose down")
        log(GREEN, "BYE", "Todo detenido.\n")


if __name__ == "__main__":
    main()
