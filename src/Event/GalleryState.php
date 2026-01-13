<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class GalleryState
     *
     * Captures the save an event that occurs after the popup is closed.
     *
     */

    class GalleryState extends EventBase {

        const string EVENT_NAME = 'gallerystate';
        const string JS_RETURN_PARAM = 'ui';
    }
