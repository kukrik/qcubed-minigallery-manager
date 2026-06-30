document.addEventListener('DOMContentLoaded', function () {
    const previewBox = document.createElement('div');
    previewBox.className = 'gallery-hover-preview';
    previewBox.innerHTML = '<img src="" alt="">';
    document.body.appendChild(previewBox);

    const previewImg = previewBox.querySelector('img');


    document.addEventListener('mouseenter', function (e) {
        const preview = e.target.closest('span.preview');

        if (!preview) return;

        const img = preview.querySelector('img');

        if (!img || !img.src) return;

        previewImg.src = img.src;
        previewBox.style.display = 'block';
    }, true);

    document.addEventListener('mousemove', function (e) {
        if (previewBox.style.display !== 'block') return;

        const offset = 18;
        let left = e.clientX + offset;
        let top = e.clientY + offset;

        const boxRect = previewBox.getBoundingClientRect();

        if (left + boxRect.width > window.innerWidth) {
            left = e.clientX - boxRect.width - offset;
        }

        if (top + boxRect.height > window.innerHeight) {
            top = e.clientY - boxRect.height - offset;
        }

        previewBox.style.left = left + 'px';
        previewBox.style.top = top + 'px';
    });

    document.addEventListener('mouseleave', function (e) {
        const preview = e.target.closest('span.preview');

        if (!preview) return;

        previewBox.style.display = 'none';
        previewImg.src = '';
    }, true);
});