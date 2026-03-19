# esegui composer install --no-dev --optimize-autoloader e npm run build
import os
import subprocess
import shutil


def run_command(command, cwd=None):
    print(f"Eseguo: {command} (in {cwd or os.getcwd()})")
    result = subprocess.run(command, shell=True, cwd=cwd)
    if result.returncode != 0:
        raise RuntimeError(f"Comando fallito: {command}")


def copytree_filtered(src, dst, ignore_patterns=None):
    if ignore_patterns is None:
        ignore_patterns = []

    def _ignore(path, names):
        ignored = set()
        for pattern in ignore_patterns:
            ignored.update({n for n in names if n == pattern or n.endswith(pattern)})
        return ignored

    shutil.copytree(src, dst, ignore=_ignore)


def main():
    base_dir = os.path.dirname(__file__)
    backend_dir = os.path.join(base_dir, "..", "backend-api")
    frontend_dir = os.path.join(base_dir, "..", "frontend-gestionale-mercatino")
    toupload_dir = os.path.join(base_dir, "..", "toupload")

    # Composer install per il backend
    run_command("composer install --no-dev --optimize-autoloader", cwd=backend_dir)

    # Build npm per il frontend
    run_command("npm run build", cwd=frontend_dir)

    # Pulisci la cartella toupload
    if os.path.exists(toupload_dir):
        shutil.rmtree(toupload_dir)
    os.makedirs(toupload_dir)

    # Copia il frontend buildato in 'nuovosito'
    frontend_dist = os.path.join(frontend_dir, "dist")
    frontend_target = os.path.join(toupload_dir, "nuovosito")
    shutil.copytree(frontend_dist, frontend_target)

    # Copia il backend in 'nuovobackend', escludendo file sensibili
    backend_target = os.path.join(toupload_dir, "nuovobackend")
    ignore_files = [
        ".env",
        ".env.example",
        ".htaccess",
        ".htpasswd",
        "phpunit.xml",
        "phpunit.xml.dist",
        ".git",
        ".gitignore",
        ".DS_Store",
    ]

    def backend_ignore(dir, files):
        return {f for f in files if f in ignore_files or f.startswith(".env")}

    shutil.copytree(backend_dir, backend_target, ignore=backend_ignore)


if __name__ == "__main__":
    main()
