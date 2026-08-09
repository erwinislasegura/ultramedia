(function () {
    const input = document.getElementById('ctaImageInput');
    const preview = document.getElementById('ctaPreview');
    const filename = document.getElementById('ctaImageName');
    let temporaryUrl = '';

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            input.value = '';
            if (filename) filename.textContent = 'Selecciona un archivo de imagen válido.';
            return;
        }

        if (temporaryUrl) URL.revokeObjectURL(temporaryUrl);
        temporaryUrl = URL.createObjectURL(file);
        preview.style.backgroundImage = `linear-gradient(90deg,#080b0ce8,#080b0c55),url("${temporaryUrl}")`;
        if (filename) filename.textContent = file.name;
    });

    window.addEventListener('pagehide', function () {
        if (temporaryUrl) URL.revokeObjectURL(temporaryUrl);
    });
})();
