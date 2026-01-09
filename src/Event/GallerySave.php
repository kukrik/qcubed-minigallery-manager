<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class GallerySave
     *
     * Captures the save an event that occurs after the popup is closed.
     *
     */

    class GallerySave extends EventBase {

        const string EVENT_NAME = 'gallerysave';
        const string JS_RETURN_PARAM = 'ui';
    }
