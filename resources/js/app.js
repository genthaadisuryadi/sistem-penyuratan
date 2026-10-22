/*
 * JavaScript sengaja dibuat ringan dan tanpa framework: setiap perilaku
 * dipasang lewat atribut data-*, sehingga aman dengan CSP `script-src 'self'`.
 */

const ready = (fn) =>
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn();

ready(() => {
    // Tampil/sembunyikan kata sandi
    document.querySelectorAll('[data-toggle-password]').forEach((tombol) => {
        const input = document.getElementById(tombol.dataset.togglePassword);
        if (!input) return;

        tombol.addEventListener('click', () => {
            const tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            tombol.setAttribute('aria-pressed', String(tampil));
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            tombol.querySelectorAll('[data-eye]').forEach((el) => {
                el.classList.toggle('hidden', (el.dataset.eye === 'on') === tampil);
            });
        });
    });

    // Menu samping (layar kecil)
    const sidebar = document.getElementById('sidebar');
    document.querySelectorAll('[data-sidebar-toggle]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const terbuka = sidebar.classList.toggle('-translate-x-full');
            tombol.setAttribute('aria-expanded', String(!terbuka));
        });
    });

    // Dialog (modal) native
    document.querySelectorAll('[data-modal-open]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const dialog = document.getElementById(tombol.dataset.modalOpen);
            if (!dialog) return;

            if (tombol.dataset.formAction) {
                dialog.querySelector('form').action = tombol.dataset.formAction;
            }
            dialog.querySelectorAll('[data-fill]').forEach((el) => {
                el.textContent = tombol.dataset[el.dataset.fill] ?? '';
            });
            dialog.showModal();
        });
    });
    document.querySelectorAll('[data-modal-close]').forEach((tombol) => {
        tombol.addEventListener('click', () => tombol.closest('dialog').close());
    });
    document.querySelectorAll('dialog[data-reopen]').forEach((dialog) => dialog.showModal());

    // Salin ke papan klip
    document.querySelectorAll('[data-copy]').forEach((tombol) => {
        tombol.addEventListener('click', async () => {
            const awal = tombol.textContent;
            try {
                await navigator.clipboard.writeText(tombol.dataset.copy);
                tombol.textContent = 'Tersalin';
            } catch {
                tombol.textContent = 'Gagal menyalin';
            }
            setTimeout(() => (tombol.textContent = awal), 1800);
        });
    });

    // Kode verifikasi: huruf besar + tanda hubung otomatis (ABCDE-FGHJK)
    document.querySelectorAll('[data-kode-input]').forEach((input) => {
        input.addEventListener('input', () => {
            const bersih = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
            input.value = bersih.length > 5 ? `${bersih.slice(0, 5)}-${bersih.slice(5)}` : bersih;
        });
    });

    // Cegah kirim ganda pada form yang prosesnya lama (mis. penerbitan surat)
    document.querySelectorAll('form[data-loading-text]').forEach((form) => {
        form.addEventListener('submit', () => {
            const tombol = form.querySelector('[type="submit"]');
            if (!tombol || form.dataset.submitted) return;
            form.dataset.submitted = '1';
            tombol.disabled = true;
            tombol.dataset.label = tombol.textContent;
            tombol.textContent = form.dataset.loadingText;
        });
    });

    // Dropzone unggah PDF
    document.querySelectorAll('[data-dropzone]').forEach((zona) => {
        const input = zona.querySelector('input[type="file"]');
        const kosong = zona.querySelector('[data-dz-empty]');
        const terisi = zona.querySelector('[data-dz-filled]');
        const nama = zona.querySelector('[data-dz-name]');
        const ukuran = zona.querySelector('[data-dz-size]');
        const galat = zona.parentElement.querySelector('[data-dz-error]');
        const maksKb = Number(zona.dataset.maxKb || 0);

        const tampilkan = (berkas) => {
            galat.textContent = '';
            if (!berkas) {
                kosong.classList.remove('hidden');
                terisi.classList.add('hidden');
                return;
            }
            const kb = berkas.size / 1024;
            if (berkas.type !== 'application/pdf' && !berkas.name.toLowerCase().endsWith('.pdf')) {
                galat.textContent = 'Berkas harus berformat PDF.';
                input.value = '';
                return tampilkan(null);
            }
            if (maksKb && kb > maksKb) {
                galat.textContent = `Ukuran berkas ${(kb / 1024).toFixed(1)} MB melebihi batas ${(maksKb / 1024).toFixed(0)} MB.`;
                input.value = '';
                return tampilkan(null);
            }
            nama.textContent = berkas.name;
            ukuran.textContent = kb > 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${Math.max(1, Math.round(kb))} KB`;
            kosong.classList.add('hidden');
            terisi.classList.remove('hidden');
        };

        ['dragenter', 'dragover'].forEach((e) =>
            zona.addEventListener(e, (ev) => {
                ev.preventDefault();
                zona.classList.add('is-over');
            }),
        );
        ['dragleave', 'drop'].forEach((e) =>
            zona.addEventListener(e, (ev) => {
                ev.preventDefault();
                zona.classList.remove('is-over');
            }),
        );
        zona.addEventListener('drop', (ev) => {
            if (ev.dataTransfer?.files?.length) {
                input.files = ev.dataTransfer.files;
                tampilkan(input.files[0]);
            }
        });
        input.addEventListener('change', () => tampilkan(input.files[0]));
        zona.querySelector('[data-dz-clear]')?.addEventListener('click', (ev) => {
            ev.preventDefault();
            input.value = '';
            tampilkan(null);
        });
        if (input.files?.length) tampilkan(input.files[0]);
    });


    // QR preview lives from the selected letter type and the typed letter number.
    document.querySelectorAll('[data-qr-live]').forEach((form) => {
        const nomor = form.querySelector('[name="nomor_surat"]');
        const jenis = form.querySelector('[name="jenis_surat_id"]');
        const output = form.querySelector('[data-live-qr]');
        const urlOutput = form.querySelector('[data-live-qr-url]');
        const downloadButton = form.querySelector('[data-download-qr]');
        if (!nomor || !jenis || !output) return;

        const unduhSvg = (markup, nama) => {
            const blob = new Blob([markup], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${nama}.svg`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        };

        const namaBerkas = () => {
            const aman = nomor.value.trim().replace(/[^a-z0-9_-]+/gi, '-').replace(/^-+|-+$/g, '').slice(0, 70);
            return `qrcode-${aman || 'verifikasi'}`;
        };

        if (downloadButton) {
            downloadButton.addEventListener('click', () => {
                const svgAsli = output.querySelector('svg');
                if (!svgAsli) return;

                const salinan = svgAsli.cloneNode(true);
                // SVG mandiri dengan ukuran persegi agar tetap tajam saat diunduh.
                salinan.setAttribute('width', '1024');
                salinan.setAttribute('height', '1024');
                const markup = new XMLSerializer().serializeToString(salinan);
                const img = new Image();
                img.onload = () => {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = 1024;
                        canvas.height = 1024;
                        const context = canvas.getContext('2d');
                        if (!context) throw new Error('Canvas tidak tersedia');
                        context.fillStyle = '#fff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                        context.imageSmoothingEnabled = false;
                        context.drawImage(img, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob((blob) => {
                            if (!blob) {
                                unduhSvg(markup, namaBerkas());
                                return;
                            }
                            const url = URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `${namaBerkas()}.png`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
                        }, 'image/png');
                    } catch {
                        unduhSvg(markup, namaBerkas());
                    }
                };
                img.onerror = () => unduhSvg(markup, namaBerkas());
                img.src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(markup)}`;
            });
        }

        let jeda;
        let controller;
        const refresh = () => {
            window.clearTimeout(jeda);
            if (downloadButton) downloadButton.disabled = true;
            jeda = window.setTimeout(async () => {
                const nilaiNomor = nomor.value.trim();
                if (!nilaiNomor || !jenis.value) {
                    output.textContent = 'Masukkan nomor surat dan pilih jenis surat untuk menampilkan QR.';
                    if (urlOutput) urlOutput.textContent = '';
                    return;
                }

                controller?.abort();
                controller = new AbortController();
                const params = new URLSearchParams({ nomor_surat: nilaiNomor, jenis_surat_id: jenis.value });
                try {
                    const response = await fetch(`${form.dataset.qrEndpoint}?${params.toString()}`, {
                        headers: { Accept: 'image/svg+xml' },
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error('QR preview unavailable');
                    const svg = await response.text();
                    output.innerHTML = svg;
                    output.querySelector('svg')?.classList.add('h-full', 'w-full');
                    if (downloadButton) downloadButton.disabled = !output.querySelector('svg');
                    if (urlOutput) {
                        const nomorPath = nilaiNomor.split('/').map((bagian) => encodeURIComponent(bagian)).join('/');
                        urlOutput.textContent = `${(form.dataset.qrBaseUrl || window.location.origin).replace(/\/+$/, '')}/verify/${nomorPath.replace(/^\/+/, '')}`;
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        output.textContent = 'QR belum dapat dibuat. Periksa nomor dan jenis surat.';
                        if (urlOutput) urlOutput.textContent = '';
                        if (downloadButton) downloadButton.disabled = true;
                    }
                }
            }, 220);
        };
        nomor.addEventListener('input', refresh);
        jenis.addEventListener('change', refresh);
        refresh();
    });
});
