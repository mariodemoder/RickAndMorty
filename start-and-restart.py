import subprocess
import sys
import time
import os

# Config
CONTAINER_LARAVEL = "quental-laravel.test-1"
CONTAINER_MYSQL = "quental-mysql-1"
COMPOSE_FILE = "compose.yaml"
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
                log(GREEN, "OK", f"{label} esta listo")
                return True
        elif condition == "Status":
            if value.startswith("running"):
                log(GREEN, "OK", f"{label} esta corriendo")
                return True
        time.sleep(HEALTH_INTERVAL)
    log(RED, "FAIL", f"Timeout esperando {label}")
    return False


def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(script_dir)

    print(f"\n{CYAN}{BOLD}{'='*50}")
    print(f"  START AND RESTART - Laravel Sail")
    print(f"{'='*50}{RESET}\n")

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
    if not wait_container(CONTAINER_MYSQL, "Health.Status", "MySQL"):
        sys.exit(1)

    # 4. Esperar Laravel corriendo
    if not wait_container(CONTAINER_LARAVEL, "Status", "Laravel"):
        sys.exit(1)

    print()
    log(GREEN, "READY", "Containers listos.\n")

    # 5. npm install
    log(YELLOW, "STEP 5", "Instalando dependencias npm...")
    result = run(f"docker exec {CONTAINER_LARAVEL} npm install")
    if result.returncode != 0:
        log(RED, "ERROR", "npm install fallo")
        sys.exit(1)
    log(GREEN, "OK", "Dependencias instaladas.\n")

    # 6. npm run dev en foreground
    log(YELLOW, "STEP 6", "Iniciando Vite...\n")
    try:
        proc = subprocess.Popen(
            f"docker exec -it {CONTAINER_LARAVEL} npm run dev",
            shell=True,
        )
        proc.wait()
    except KeyboardInterrupt:
        pass
    finally:
        print()
        log(YELLOW, "STOP", "Deteniendo containers...")
        run("docker compose down")
        log(GREEN, "BYE", "Todo detenido.\n")


if __name__ == "__main__":
    main()
