(function ($) {
    $.fn.nanoGalleryCover = function (options) {
        const settings = $.extend(true, {
            coverSelector: null,
            startItem: "0/1",
            nanoOptions: {},
            debug: false
        }, options || {});

        function log(...args) {
            if (settings.debug && window.console) console.log("[nanoGalleryCover]", ...args);
        }

        return this.each(function () {
            const $gallery = $(this);

            if ($gallery.data("ngCoverInit")) {
                log("Already initialized:", $gallery.attr("id"));
                return;
            }

            // 1) Init nanogallery2 exactly once
            if (typeof $.fn.nanogallery2 !== "function") {
                console.error("nanogallery2 plugin is not loaded.");
                return;
            }

            $gallery.nanogallery2(settings.nanoOptions);
            $gallery.data("ngCoverInit", true);

            // 2) Bind cover click (namespaced)
            if (!settings.coverSelector) {
                console.error("coverSelector is required.");
                return;
            }

            const $cover = $(settings.coverSelector);
            if (!$cover.length) {
                console.warn("Cover element not found:", settings.coverSelector);
                return;
            }

            $cover.off("click.nanoGalleryCover").on("click.nanoGalleryCover", function (e) {
                e.preventDefault();
                e.stopPropagation();
                log("Cover clicked -> displayItem", settings.startItem);

                try {
                    $gallery.nanogallery2("displayItem", settings.startItem);
                } catch (err) {
                    console.error("displayItem failed:", err);
                }
            });
        });
    };
})(jQuery);
