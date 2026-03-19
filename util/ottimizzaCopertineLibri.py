import os
import subprocess
import multiprocessing
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading


def print_progress(count, total, bar_width=40):
    percent = int(count * 100 / total)
    filled = int(percent * bar_width / 100)
    empty = bar_width - filled
    bar = f"[{'#' * filled}{'-' * empty}] {percent:3d}% ({count}/{total})"
    print(f"\r{bar}", end="", flush=True)


def run_command(command, cwd=None):
    try:
        result = subprocess.run(
            command,
            shell=True,
            cwd=cwd,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        if result.returncode != 0:
            raise RuntimeError(f"Comando fallito: {command}")
    except Exception as e:
        print(f"\n[ERROR] Errore eseguendo comando '{command}': {e}")


def process_image(f, base_dir, jpg_out, avif_out, webp_out):
    name = Path(f).stem
    src = base_dir / f
    jpg_opt = jpg_out / f"{name}.jpg"
    run_command(
        f"magick '{src}' -resize 100x140^ -gravity center -extent 100x140 -strip '{jpg_opt}'"
    )
    run_command(f"jpegoptim '{jpg_opt}'")
    # AVIF (meno compressione)
    avif_final = avif_out / f"{name}.avif"
    run_command(f"avifenc '{jpg_opt}' '{avif_final}'")
    # WEBP (più qualità)
    webp_final = webp_out / f"{name}.webp"
    run_command(f"cwebp '{jpg_opt}' -o '{webp_final}'")


def main():
    # Cartella immagini di toupload
    base_dir = Path(__file__).resolve().parent.parent / "toupload" / "imgs" / "libri"
    jpg_out = base_dir / "jpg_ottimizzati"
    avif_out = base_dir / "avif_ottimizzati"
    webp_out = base_dir / "webp_ottimizzati"
    os.makedirs(jpg_out, exist_ok=True)
    os.makedirs(avif_out, exist_ok=True)
    os.makedirs(webp_out, exist_ok=True)
    # Solo JPG nella cartella immagini (no sottocartelle)
    files = sorted(
        f
        for f in os.listdir(base_dir)
        if f.lower().endswith(".jpg") and (base_dir / f).is_file()
    )
    total = len(files)
    if total == 0:
        print(f"Nessun file JPG trovato in {base_dir}.")
        return

    # Calcola numero thread (75% dei core)
    max_workers = max(1, int(multiprocessing.cpu_count() * 0.75))
    count = 0
    count_lock = threading.Lock()

    def wrapped_process(f):
        nonlocal count
        process_image(f, base_dir, jpg_out, avif_out, webp_out)
        with count_lock:
            count += 1
            print_progress(count, total)

    print_progress(0, total)
    with ThreadPoolExecutor(max_workers=max_workers) as executor:
        futures = [executor.submit(wrapped_process, f) for f in files]
        for _ in as_completed(futures):
            pass
    print()  # Newline after progress bar
    print("Done.")


if __name__ == "__main__":
    main()
