function syncMiniGalleryCoverState(previewId = null) {
    const checkboxes = document.querySelectorAll(
        '.js-gallery-table input[type="checkbox"]'
    );

    checkboxes.forEach(input => {
        const idParts = input.id.split('_');
        const itemId = parseInt(idParts[idParts.length - 1], 10);
        const wrapper = input.closest('.checkbox');

        if (previewId && itemId === previewId) {
            input.checked = true;
            input.disabled = false;
            wrapper?.classList.remove('disabled');
        } else if (previewId) {
            input.checked = false;
            input.disabled = true;
            wrapper?.classList.add('disabled');
        } else {
            input.checked = false;
            input.disabled = false;
            wrapper?.classList.remove('disabled');
        }
    });
}
