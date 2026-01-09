<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class ImageSave
     *
     * Captures the save an event that occurs after the popup is closed.
     *
     */

    class ImageSave extends EventBase {

        const string EVENT_NAME = 'imagesave';
        const string JS_RETURN_PARAM = 'ui';
    }
